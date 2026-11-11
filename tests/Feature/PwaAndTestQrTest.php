<?php

namespace Tests\Feature;

use App\Models\Step;
use Database\Seeders\StepSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaAndTestQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_test_qr_view_renders_successfully_with_all_six_steps_and_codes(): void
    {
        $this->seed(StepSeeder::class);

        $response = $this->get('/test-qr');

        $response->assertStatus(200);
        $response->assertSee('Mire de Test des QR Codes — Expédition Lens');
        $response->assertSee('Retour à l\'application', false);

        $expectedSteps = [
            1 => [
                'name' => 'Étape 1 : Gare de Lens',
                'qr_map' => 'MAP_LOC_1',
                'qr_mj' => 'MJ_STEP_1',
                'code_part' => 'Bord de l\'anneau',
                'riddle' => 'Au dos du morceau de clé trouvé dans le grenier, Gali lit sur une vieille note : « Cherche l\'endroit où les trains se reposent… » Repère cette grande locomotive immobile figée dans la pierre !',
            ],
            2 => [
                'name' => 'Étape 2 : Centre-Ville',
                'qr_map' => 'MAP_LOC_2',
                'qr_mj' => 'MJ_STEP_2',
                'code_part' => 'Base de l\'anneau',
                'riddle' => 'Sur les traces de la vie dans les corons, plonge au cœur de la cité. Utilise la loupe du carnet pour repérer le détail caché sur une façade !',
            ],
            3 => [
                'name' => 'Étape 3 : Stade Bollaert-Delelis',
                'qr_map' => 'MAP_LOC_3',
                'qr_mj' => 'MJ_STEP_3',
                'code_part' => 'Col sculpté',
                'riddle' => 'Là où vibre la passion Sang et Or ! Résous l\'énigme autour des couleurs de la ville pour retrouver ce temple légendaire.',
            ],
            4 => [
                'name' => 'Étape 4 : Musée Louvre-Lens',
                'qr_map' => 'MAP_LOC_4',
                'qr_mj' => 'MJ_STEP_4',
                'code_part' => 'Fût gravé',
                'riddle' => 'Quand l\'ancienne mine laisse place aux reflets de verre et d\'aluminium… Un grand trésor culturel veille au milieu d\'un vaste parc.',
            ],
            5 => [
                'name' => 'Étape 5 : Les Terrils de Loos-en-Gohelle',
                'qr_map' => 'MAP_LOC_5',
                'qr_mj' => 'MJ_STEP_5',
                'code_part' => 'Garde du fût',
                'riddle' => 'Grimpe vers les deux géants noirs jumeaux qui dominent l\'horizon minier pour récupérer le cinquième fragment de la clé !',
            ],
            6 => [
                'name' => 'Étape 6 : Office de Tourisme',
                'qr_map' => 'MAP_LOC_6',
                'qr_mj' => 'MJ_STEP_6',
                'code_part' => 'Dents de la clé',
                'riddle' => 'La clé est presque complète et ses repères convergent vers le point d\'accueil des voyageurs : décroche le sixième et dernier fragment pour ouvrir le coffre secret et rallumer la Lampe Cœur !',
            ],
        ];

        foreach ($expectedSteps as $id => $data) {
            $response->assertSee($data['name']);
            $response->assertSee($data['riddle']);
            $response->assertSee($data['qr_map']);
            $response->assertSee($data['qr_mj']);
            $response->assertSee($data['code_part']);

            // Verify QR generator API URLs are rendered for both Phase A (Map) and Phase B (MJ)
            $response->assertSee('https://api.qrserver.com/v1/create-qr-code/?size=180x180&amp;margin=10&amp;data='.$data['qr_map'], false);
            $response->assertSee('https://api.qrserver.com/v1/create-qr-code/?size=180x180&amp;margin=10&amp;data='.$data['qr_mj'], false);
        }

        // Verify cheat sheet summary and key treasure outcome
        $response->assertSee('Récapitulatif des codes');
        $response->assertSee('Clé Complète (6 fragments de clé : Étapes 1 à 6)');
        $response->assertSee('Participation automatique au Grand Tirage au Sort !');
        $response->assertDontSee('4819');
        $response->assertDontSee('Coffre Asmodee');
    }

    public function test_test_qr_view_auto_seeds_when_steps_table_is_empty(): void
    {
        // Database is fresh and empty (no seed run before request)
        $this->assertEquals(0, Step::count());

        $response = $this->get('/test-qr');

        $response->assertStatus(200);
        $this->assertEquals(6, Step::count());
        $response->assertSee('Étape 1 : Gare de Lens');
        $response->assertSee('MAP_LOC_1');
        $response->assertSee('MJ_STEP_1');
    }

    public function test_manifest_webmanifest_is_served_and_contains_pwa_fields(): void
    {
        $response = $this->get('/manifest.webmanifest');

        $response->assertStatus(200);

        $json = json_decode($response->getContent(), true);
        $this->assertIsArray($json);
        $this->assertEquals("L'Expédition Secrète - Lens", $json['name']);
        $this->assertEquals('Expédition Lens', $json['short_name']);
        $this->assertEquals('standalone', $json['display']);
        $this->assertEquals('portrait', $json['orientation']);
        $this->assertEquals('#000000', $json['theme_color']);
        $this->assertEquals('#000000', $json['background_color']);
        $this->assertEquals('/', $json['start_url']);
        $this->assertNotEmpty($json['icons']);
        $this->assertEquals('/icon.svg', $json['icons'][0]['src']);
    }

    public function test_app_layout_registers_pwa_manifest_and_ios_meta_tags(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('<link rel="manifest" href="/manifest.webmanifest">', false);
        $response->assertSee('<meta name="theme-color" content="#000000">', false);
        $response->assertSee('<meta name="apple-mobile-web-app-capable" content="yes">', false);
        $response->assertSee('<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">', false);
        $response->assertSee('<meta name="apple-mobile-web-app-title" content="Expédition Lens">', false);
        $response->assertSee('<meta name="mobile-web-app-capable" content="yes">', false);
        $response->assertSee('<link rel="apple-touch-icon" href="/icon.svg">', false);
    }

    public function test_pwa_icon_svg_exists_and_is_served(): void
    {
        $response = $this->get('/icon.svg');

        $response->assertStatus(200);
        $this->assertStringContainsString('image/svg+xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('<svg', $response->getContent());
        $this->assertStringContainsString('</svg>', $response->getContent());
    }
}
