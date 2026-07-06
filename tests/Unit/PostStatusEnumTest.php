<?php

namespace Tests\Unit;

use App\Enums\PostStatusEnum;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour PostStatusEnum.
 *
 * Contrairement aux feature tests qui envoient de vraies requêtes HTTP,
 * un unit test teste une portion isolée du code sans démarrer Laravel.
 *
 * Ici on étend PHPUnit\Framework\TestCase (pas Tests\TestCase)
 * car on n'a pas besoin de la base de données ni du container Laravel.
 */
class PostStatusEnumTest extends TestCase
{
    /**
     * Test que l'énumération contient bien les 3 cas attendus.
     *
     * On vérifie que les valeurs string correspondent à ce qui est
     * stocké en base et utilisé par le contrôleur PostController.
     */
    public function test_enum_has_three_cases(): void
    {
        try {
            $cases = PostStatusEnum::cases();

            $this->assertCount(3, $cases);
            $this->assertContains(PostStatusEnum::Draft, $cases);
            $this->assertContains(PostStatusEnum::Archived, $cases);
            $this->assertContains(PostStatusEnum::Posted, $cases);
        } catch (\Throwable $e) {
            $this->fail('Echec test cas enum : ' . $e->getMessage());
        }
    }

    /**
     * Test que chaque cas a la bonne valeur string associée.
     *
     * Ces valeurs sont utilisées dans :
     *   - Les migrations (colonne statut_publication)
     *   - Les factories (PostFactory)
     *   - Le contrôleur PostController pour la validation
     */
    public function test_enum_values_are_correct(): void
    {
        try {
            $this->assertEquals('draft', PostStatusEnum::Draft->value);
            $this->assertEquals('archived', PostStatusEnum::Archived->value);
            $this->assertEquals('posted', PostStatusEnum::Posted->value);
        } catch (\Throwable $e) {
            $this->fail('Echec test valeurs enum : ' . $e->getMessage());
        }
    }

    /**
     * Test que l'énumération peut être utilisée comme type Eloquent cast.
     *
     * Le modèle Post cast la colonne 'statut_publication' vers
     * PostStatusEnum. On vérifie que from() et tryFrom() marchent.
     */
    public function test_enum_can_be_created_from_string(): void
    {
        try {
            $draft = PostStatusEnum::from('draft');
            $this->assertInstanceOf(PostStatusEnum::class, $draft);
            $this->assertEquals(PostStatusEnum::Draft, $draft);

            $archived = PostStatusEnum::tryFrom('archived');
            $this->assertInstanceOf(PostStatusEnum::class, $archived);
            $this->assertEquals(PostStatusEnum::Archived, $archived);

            $null = PostStatusEnum::tryFrom('invalid_status');
            $this->assertNull($null);
        } catch (\Throwable $e) {
            $this->fail('Echec test conversion string → enum : ' . $e->getMessage());
        }
    }
}
