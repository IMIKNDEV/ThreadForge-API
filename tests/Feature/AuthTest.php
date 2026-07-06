<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login avec des identifiants valides.
     *
     * On crée un utilisateur en base, puis on POST /api/login
     * avec email + password corrects.
     * Le contrôleur AuthController::login() utilise Auth::attempt()
     * pour vérifier les identifiants, puis createToken() pour générer
     * un token Sanctum.
     *
     * Résultat attendu : 200 + structure JSON {data, token, token_type}
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        try {
            // Arrange : créer un utilisateur avec un email et mot de passe connus
            $user = User::factory()->create([
                'email'    => 'john@example.com',
                'password' => bcrypt('password123'),
            ]);

            // Act : tenter la connexion
            $response = $this->postJson('/api/login', [
                'email'    => 'john@example.com',
                'password' => 'password123',
            ]);

            // Assert : 200 + clés JSON attendues
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data'       => ['id', 'name', 'email', 'created_at'],
                    'token',
                    'token_type',
                ])
                ->assertJsonPath('token_type', 'Bearer');
        } catch (\Throwable $e) {
            $this->fail('Echec du test login valide : ' . $e->getMessage());
        }
    }

    /**
     * Test login avec un mauvais mot de passe.
     *
     * On crée un utilisateur, puis on POST /api/login avec
     * le bon email mais un mauvais password.
     * Auth::attempt() doit retourner false → 401.
     *
     * Résultat attendu : 401 + {"message": "Invalid credentials"}
     */
    public function test_user_cannot_login_with_invalid_password(): void
    {
        try {
            // Arrange : créer un utilisateur
            $user = User::factory()->create([
                'email'    => 'john@example.com',
                'password' => bcrypt('password123'),
            ]);

            // Act : tenter la connexion avec un mauvais mot de passe
            $response = $this->postJson('/api/login', [
                'email'    => 'john@example.com',
                'password' => 'wrongpassword',
            ]);

            // Assert : 401 avec message d'erreur
            $response->assertStatus(401)
                ->assertJsonPath('message', 'Invalid credentials');
        } catch (\Throwable $e) {
            $this->fail('Echec du test login invalide : ' . $e->getMessage());
        }
    }
}
