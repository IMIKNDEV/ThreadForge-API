<?php

namespace Tests\Feature;

use App\Models\Blueprint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlueprintTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_blueprints(): void
    {
        $response = $this->getJson('/api/blueprints');
        $response->assertStatus(401);

        $response = $this->postJson('/api/blueprints', []);
        $response->assertStatus(401);

        $response = $this->getJson('/api/blueprints/1');
        $response->assertStatus(401);
    }

    public function test_user_can_create_blueprint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $payload = [
            'name' => 'Test Blueprint',
            'tone' => 'Professional',
            'max_hashtag' => 5,
            'max_characters' => 280,
            'banned_word' => 'scam',
            'extra_rules' => 'Be concise.',
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
            'name' => 'Test Blueprint',
            'user_id' => $user->id,
        ]);
    }

    public function test_create_blueprint_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/blueprints', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'tone', 'max_hashtag', 'max_characters']);
    }

    public function test_user_can_list_their_blueprints(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        Blueprint::factory()->count(3)->create(['user_id' => $user->id]);
        Blueprint::factory()->count(2)->create();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/blueprints');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_see_their_blueprint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $blueprint = Blueprint::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/blueprints/{$blueprint->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $blueprint->id);
    }

    public function test_user_gets_404_for_others_blueprint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $otherBlueprint = Blueprint::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/blueprints/{$otherBlueprint->id}");

        $response->assertStatus(404);
    }

    public function test_user_can_update_their_blueprint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $blueprint = Blueprint::factory()->create([
            'user_id' => $user->id,
            'name' => 'Original',
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/blueprints/{$blueprint->id}", [
                'name' => 'Updated',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated');

        $this->assertDatabaseHas('blueprints', [
            'id' => $blueprint->id,
            'name' => 'Updated',
        ]);
    }

    public function test_user_gets_404_when_updating_others_blueprint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $otherBlueprint = Blueprint::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/blueprints/{$otherBlueprint->id}", [
                'name' => 'Hacked',
            ]);

        $response->assertStatus(404);
    }

    public function test_user_can_delete_their_blueprint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $blueprint = Blueprint::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/blueprints/{$blueprint->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Blueprint deleted');

        $this->assertDatabaseMissing('blueprints', ['id' => $blueprint->id]);
    }

    public function test_user_gets_404_when_deleting_others_blueprint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $otherBlueprint = Blueprint::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/blueprints/{$otherBlueprint->id}");

        $response->assertStatus(404);
    }
}
