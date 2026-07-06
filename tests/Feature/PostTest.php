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

    /*
    |--------------------------------------------------------------------------
    | Tests existants (enrichis avec try-catch + commentaires)
    |--------------------------------------------------------------------------
    */

    public function test_unauthenticated_user_cannot_access_posts(): void
    {
        try {
            $response = $this->getJson('/api/posts');
            $response->assertStatus(401);
        } catch (\Throwable $e) {
            $this->fail('Echec test 401 posts : ' . $e->getMessage());
        }
    }

    public function test_user_can_list_their_posts(): void
    {
        try {
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
        } catch (\Throwable $e) {
            $this->fail('Echec test liste posts : ' . $e->getMessage());
        }
    }

    public function test_user_can_filter_posts_by_status(): void
    {
        try {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;
            $rawContent = RawContent::factory()->create(['user_id' => $user->id]);

            Post::factory()->create([
                'raw_content_id'    => $rawContent->id,
                'statut_publication' => PostStatusEnum::Draft,
            ]);
            Post::factory()->create([
                'raw_content_id'    => $rawContent->id,
                'statut_publication' => PostStatusEnum::Posted,
            ]);

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->getJson('/api/posts?status=posted');

            $response->assertStatus(200)
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.statut_publication', 'posted');
        } catch (\Throwable $e) {
            $this->fail('Echec test filtre posts : ' . $e->getMessage());
        }
    }

    public function test_user_can_see_their_post(): void
    {
        try {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;
            $rawContent = RawContent::factory()->create(['user_id' => $user->id]);
            $post = Post::factory()->create(['raw_content_id' => $rawContent->id]);

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->getJson("/api/posts/{$post->id}");

            $response->assertStatus(200)
                ->assertJsonPath('data.id', $post->id);
        } catch (\Throwable $e) {
            $this->fail('Echec test voir post : ' . $e->getMessage());
        }
    }

    public function test_user_gets_404_for_others_post(): void
    {
        try {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;
            $otherPost = Post::factory()->create();

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->getJson("/api/posts/{$otherPost->id}");

            $response->assertStatus(404);
        } catch (\Throwable $e) {
            $this->fail('Echec test 404 post autre user : ' . $e->getMessage());
        }
    }

    public function test_user_can_update_post_status(): void
    {
        try {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;
            $rawContent = RawContent::factory()->create(['user_id' => $user->id]);
            $post = Post::factory()->create([
                'raw_content_id'    => $rawContent->id,
                'statut_publication' => PostStatusEnum::Draft,
            ]);

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->patchJson("/api/posts/{$post->id}", [
                    'status' => 'posted',
                ]);

            $response->assertStatus(200)
                ->assertJsonPath('data.statut_publication', 'posted');
        } catch (\Throwable $e) {
            $this->fail('Echec test update post : ' . $e->getMessage());
        }
    }

    public function test_user_gets_404_when_updating_others_post(): void
    {
        try {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;
            $otherPost = Post::factory()->create();

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->patchJson("/api/posts/{$otherPost->id}", [
                    'status' => 'posted',
                ]);

            $response->assertStatus(404);
        } catch (\Throwable $e) {
            $this->fail('Echec test 404 update autre post : ' . $e->getMessage());
        }
    }

    public function test_update_post_validates_status_field(): void
    {
        try {
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
        } catch (\Throwable $e) {
            $this->fail('Echec test validation status : ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Bonus : post archivé
    |--------------------------------------------------------------------------
    */

    /**
     * Test bonus : vérifier qu'on peut archiver un post.
     *
     * On crée un post en statut "draft", on le met à jour avec
     * le statut "archived". Le contrôleur PostController::update()
     * accepte la valeur 'archived' via la validation dans
     * PostStatusEnum.
     */
    public function test_user_can_archive_a_post(): void
    {
        try {
            $user = User::factory()->create();
            $token = $user->createToken('test')->plainTextToken;
            $rawContent = RawContent::factory()->create(['user_id' => $user->id]);
            $post = Post::factory()->create([
                'raw_content_id'     => $rawContent->id,
                'statut_publication' => PostStatusEnum::Draft,
            ]);

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->patchJson("/api/posts/{$post->id}", [
                    'status' => 'archived',
                ]);

            $response->assertStatus(200)
                ->assertJsonPath('data.statut_publication', 'archived');
        } catch (\Throwable $e) {
            $this->fail('Echec test archivage post : ' . $e->getMessage());
        }
    }
}
