<?php

namespace Tests\Feature;

use App\Enums\PostStatusEnum;
use App\Models\Post;
use App\Models\RawContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_posts(): void
    {
        $response = $this->getJson('/api/posts');
        $response->assertStatus(401);
    }

    public function test_user_can_list_their_posts(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $rawContent = RawContent::factory()->create(['user_id' => $user->id]);
        Post::factory()->count(3)->create(['raw_content_id' => $rawContent->id]);

        $otherUser = User::factory()->create();
        $otherRaw = RawContent::factory()->create(['user_id' => $otherUser->id]);
        Post::factory()->count(2)->create(['raw_content_id' => $otherRaw->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_filter_posts_by_status(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $rawContent = RawContent::factory()->create(['user_id' => $user->id]);

        Post::factory()->create([
            'raw_content_id' => $rawContent->id,
            'statut_publication' => PostStatusEnum::Draft,
        ]);
        Post::factory()->create([
            'raw_content_id' => $rawContent->id,
            'statut_publication' => PostStatusEnum::Posted,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/posts?status=posted');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.statut_publication', 'posted');
    }

    public function test_user_can_see_their_post(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $rawContent = RawContent::factory()->create(['user_id' => $user->id]);
        $post = Post::factory()->create(['raw_content_id' => $rawContent->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $post->id);
    }

    public function test_user_gets_404_for_others_post(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $otherPost = Post::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/posts/{$otherPost->id}");

        $response->assertStatus(404);
    }

    public function test_user_can_update_post_status(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $rawContent = RawContent::factory()->create(['user_id' => $user->id]);
        $post = Post::factory()->create([
            'raw_content_id' => $rawContent->id,
            'statut_publication' => PostStatusEnum::Draft,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/posts/{$post->id}", [
                'status' => 'posted',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.statut_publication', 'posted');
    }

    public function test_user_gets_404_when_updating_others_post(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $otherPost = Post::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/posts/{$otherPost->id}", [
                'status' => 'posted',
            ]);

        $response->assertStatus(404);
    }

    public function test_update_post_validates_status_field(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $rawContent = RawContent::factory()->create(['user_id' => $user->id]);
        $post = Post::factory()->create(['raw_content_id' => $rawContent->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/posts/{$post->id}", [
                'status' => 'invalid_status',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }
}
