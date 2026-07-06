<?php

namespace Tests\Feature;

use App\Jobs\GeneratePostJob;
use App\Models\Blueprint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RawContentTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Tests obligatoires (Phase 0)
    |--------------------------------------------------------------------------
    */

    /**
     * Test POST /api/content/repurpose → 202 + Queue::fake() + assertPushed().
     *
     * On fake la queue pour éviter d'exécuter le vrai job (qui appellerait
     * l'API Groq — lent, payant, non-déterministe).
     * On vérifie que le job GeneratePostJob est bien pushé dans la queue.
     *
     * Résultat attendu : 202 + message + raw_content_id.
     */
    public function test_submit_content_returns_202_and_dispatches_job(): void
    {
        try {
            // On fake la queue : les jobs sont interceptés, pas exécutés
            Queue::fake();

            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;
            $blueprint = Blueprint::factory()->create(['user_id' => $user->id]);

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->postJson('/api/content/repurpose', [
                    'body'         => 'Laravel 13 is amazing with new AI features.',
                    'blueprint_id' => $blueprint->id,
                ]);

            $response->assertStatus(202)
                ->assertJsonStructure(['message', 'raw_content_id'])
                ->assertJsonPath('message', 'Content received. Generation in progress.');

            // On vérifie que le job a été dispatché (pas exécuté car fake)
            Queue::assertPushed(GeneratePostJob::class);
        } catch (\Throwable $e) {
            $this->fail('Echec test content repurpose : ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Tests existants (enrichis avec try-catch)
    |--------------------------------------------------------------------------
    */

    public function test_unauthenticated_user_cannot_submit_content(): void
    {
        try {
            $response = $this->postJson('/api/content/repurpose', []);
            $response->assertStatus(401);
        } catch (\Throwable $e) {
            $this->fail('Echec test 401 content repurpose : ' . $e->getMessage());
        }
    }

    public function test_submit_content_returns_404_for_others_blueprint(): void
    {
        try {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;
            $otherBlueprint = Blueprint::factory()->create();

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->postJson('/api/content/repurpose', [
                    'body'         => 'Some content.',
                    'blueprint_id' => $otherBlueprint->id,
                ]);

            $response->assertStatus(404);
        } catch (\Throwable $e) {
            $this->fail('Echec test 404 blueprint autre : ' . $e->getMessage());
        }
    }

    public function test_submit_content_validates_required_fields(): void
    {
        try {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->postJson('/api/content/repurpose', []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['body', 'blueprint_id']);
        } catch (\Throwable $e) {
            $this->fail('Echec test validation content : ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Bonus : contenu vide
    |--------------------------------------------------------------------------
    */

    /**
     * Test bonus : envoyer un body vide (chaîne vide).
     *
     * La validation dans StoreRawContentRequest interdit les chaînes
     * vides car la règle est 'required' + 'string'.
     * Résultat attendu : 422 avec erreur sur 'body'.
     */
    public function test_empty_content_returns_422(): void
    {
        try {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;
            $blueprint = Blueprint::factory()->create(['user_id' => $user->id]);

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->postJson('/api/content/repurpose', [
                    'body'         => '',
                    'blueprint_id' => $blueprint->id,
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['body']);
        } catch (\Throwable $e) {
            $this->fail('Echec test contenu vide : ' . $e->getMessage());
        }
    }
}
