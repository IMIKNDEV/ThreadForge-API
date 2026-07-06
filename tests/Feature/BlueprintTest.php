<?php

namespace Tests\Feature;

use App\Models\Blueprint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BlueprintTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Tests d'authentification (Phase 0 — obligatoire)
    |--------------------------------------------------------------------------
    */

    /**
     * Test GET /api/blueprints sans token → 401.
     *
     * On envoie une requête GET sans aucun header d'authentification.
     * Le middleware auth:sanctum doit rejeter la requête.
     */
    public function test_unauthenticated_user_cannot_access_blueprints(): void
    {
        try {
            $response = $this->getJson('/api/blueprints');
            $response->assertStatus(401);
        } catch (\Throwable $e) {
            $this->fail('Echec test 401 GET /api/blueprints : ' . $e->getMessage());
        }
    }

    /**
     * Test GET /api/blueprints avec Sanctum::actingAs → 200 + structure JSON.
     *
     * Sanctum::actingAs($user) simule un utilisateur authentifié
     * sans passer par un vrai token. On crée 2 blueprints pour cet user,
     * puis on vérifie que la réponse contient un tableau "data" de 2
     * éléments avec toutes les clés attendues.
     */
    public function test_authenticated_user_can_list_blueprints_with_sanctum_acting_as(): void
    {
        try {
            $user = User::factory()->create();
            Sanctum::actingAs($user);
            Blueprint::factory()->count(2)->create(['user_id' => $user->id]);

            $response = $this->getJson('/api/blueprints');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id', 'name', 'tone', 'max_hashtag', 'max_characters',
                            'banned_word', 'extra_rules', 'raw_contents_count', 'created_at',
                        ],
                    ],
                ])
                ->assertJsonCount(2, 'data');
        } catch (\Throwable $e) {
            $this->fail('Echec test listing blueprints : ' . $e->getMessage());
        }
    }

    /**
     * Test POST /api/blueprints avec champs obligatoires manquants → 422.
     *
     * On envoie un payload vide. La FormRequest StoreBlueprintRequest
     * valide que name, tone, max_hashtag et max_characters sont requis.
     * Résultat attendu : 422 + erreurs de validation sur ces 4 champs.
     */
    public function test_create_blueprint_validates_required_fields(): void
    {
        try {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->postJson('/api/blueprints', []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'tone', 'max_hashtag', 'max_characters']);
        } catch (\Throwable $e) {
            $this->fail('Echec test validation blueprint : ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Tests CRUD (existants, enrichis avec try-catch)
    |--------------------------------------------------------------------------
    */

    public function test_user_can_create_blueprint(): void
    {
        try {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;

            $payload = [
                'name'          => 'Test Blueprint',
                'tone'          => 'Professional',
                'max_hashtag'   => 5,
                'max_characters'=> 280,
                'banned_word'   => 'scam',
                'extra_rules'   => 'Be concise.',
            ];

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->postJson('/api/blueprints', $payload);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'name', 'tone', 'max_hashtag', 'max_characters',
                        'banned_word', 'extra_rules', 'raw_contents_count', 'created_at',
                    ],
                ])
                ->assertJsonPath('data.name', 'Test Blueprint')
                ->assertJsonPath('data.raw_contents_count', 0);

            $this->assertDatabaseHas('blueprints', [
                'name'    => 'Test Blueprint',
                'user_id' => $user->id,
            ]);
        } catch (\Throwable $e) {
            $this->fail('Echec test création blueprint : ' . $e->getMessage());
        }
    }

    public function test_user_can_list_their_blueprints(): void
    {
        try {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;

            Blueprint::factory()->count(3)->create(['user_id' => $user->id]);
            Blueprint::factory()->count(2)->create();

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->getJson('/api/blueprints');

            $response->assertStatus(200)
                ->assertJsonCount(3, 'data');
        } catch (\Throwable $e) {
            $this->fail('Echec test listing user blueprints : ' . $e->getMessage());
        }
    }

    public function test_user_can_see_their_blueprint(): void
    {
        try {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;
            $blueprint = Blueprint::factory()->create(['user_id' => $user->id]);

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->getJson("/api/blueprints/{$blueprint->id}");

            $response->assertStatus(200)
                ->assertJsonPath('data.id', $blueprint->id);
        } catch (\Throwable $e) {
            $this->fail('Echec test voir blueprint : ' . $e->getMessage());
        }
    }

    public function test_user_gets_404_for_others_blueprint(): void
    {
        try {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;
            $otherBlueprint = Blueprint::factory()->create();

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->getJson("/api/blueprints/{$otherBlueprint->id}");

            $response->assertStatus(404);
        } catch (\Throwable $e) {
            $this->fail('Echec test 404 blueprint autre user : ' . $e->getMessage());
        }
    }

    public function test_user_can_update_their_blueprint(): void
    {
        try {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;
            $blueprint = Blueprint::factory()->create([
                'user_id' => $user->id,
                'name'    => 'Original',
            ]);

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->putJson("/api/blueprints/{$blueprint->id}", [
                    'name' => 'Updated',
                ]);

            $response->assertStatus(200)
                ->assertJsonPath('data.name', 'Updated');

            $this->assertDatabaseHas('blueprints', [
                'id'   => $blueprint->id,
                'name' => 'Updated',
            ]);
        } catch (\Throwable $e) {
            $this->fail('Echec test mise à jour blueprint : ' . $e->getMessage());
        }
    }

    public function test_user_gets_404_when_updating_others_blueprint(): void
    {
        try {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;
            $otherBlueprint = Blueprint::factory()->create();

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->putJson("/api/blueprints/{$otherBlueprint->id}", [
                    'name' => 'Hacked',
                ]);

            $response->assertStatus(404);
        } catch (\Throwable $e) {
            $this->fail('Echec test 404 update autre blueprint : ' . $e->getMessage());
        }
    }

    public function test_user_can_delete_their_blueprint(): void
    {
        try {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;
            $blueprint = Blueprint::factory()->create(['user_id' => $user->id]);

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->deleteJson("/api/blueprints/{$blueprint->id}");

            $response->assertStatus(200)
                ->assertJsonPath('message', 'Blueprint deleted');

            $this->assertDatabaseMissing('blueprints', ['id' => $blueprint->id]);
        } catch (\Throwable $e) {
            $this->fail('Echec test suppression blueprint : ' . $e->getMessage());
        }
    }

    public function test_user_gets_404_when_deleting_others_blueprint(): void
    {
        try {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;
            $otherBlueprint = Blueprint::factory()->create();

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->deleteJson("/api/blueprints/{$otherBlueprint->id}");

            $response->assertStatus(404);
        } catch (\Throwable $e) {
            $this->fail('Echec test 404 delete autre blueprint : ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Bonus : blueprint inexistant
    |--------------------------------------------------------------------------
    */

    /**
     * Test bonus : tenter d'accéder à un blueprint qui n'existe pas.
     *
     * La route utilise le binding implicite de Laravel (Route Model Binding).
     * Si l'ID ne correspond à aucun enregistrement, Laravel renvoie
     * automatiquement une 404.
     */
    public function test_nonexistent_blueprint_returns_404(): void
    {
        try {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $response = $this->getJson('/api/blueprints/99999');

            $response->assertStatus(404);
        } catch (\Throwable $e) {
            $this->fail('Echec test blueprint inexistant : ' . $e->getMessage());
        }
    }
}
