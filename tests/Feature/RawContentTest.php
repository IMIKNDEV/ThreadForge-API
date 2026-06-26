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

    public function test_unauthenticated_user_cannot_submit_content(): void
    {
        $response = $this->postJson('/api/content/repurpose', []);
        $response->assertStatus(401);
    }

    public function test_submit_content_returns_202_and_dispatches_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $blueprint = Blueprint::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/content/repurpose', [
                'body' => 'Laravel 13 is amazing with new AI features.',
                'blueprint_id' => $blueprint->id,
            ]);

        $response->assertStatus(202)
            ->assertJsonStructure(['message', 'raw_content_id'])
            ->assertJsonPath('message', 'Content received. Generation in progress.');

        Queue::assertPushed(GeneratePostJob::class);
    }

    public function test_submit_content_returns_404_for_others_blueprint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $otherBlueprint = Blueprint::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/content/repurpose', [
                'body' => 'Some content.',
                'blueprint_id' => $otherBlueprint->id,
            ]);

        $response->assertStatus(404);
    }

    public function test_submit_content_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/content/repurpose', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['body', 'blueprint_id']);
    }
}
