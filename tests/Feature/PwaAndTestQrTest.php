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
                'name' => 'Étape 1 : Place Jean Jaurès',
                'qr_map' => 'MAP_LOC_1',
                'qr_mj' => 'MJ_STEP_1',
                'code_part' => '4',
                'riddle' => 'Cherchez l\'homme de bronze qui veille sur la grande place.',
            ],
            2 => [
                'name' => 'Étape 2 : Gare de Lens',
                'qr_map' => 'MAP_LOC_2',
                'qr_mj' => 'MJ_STEP_2',
                'code_part' => '8',
                'riddle' => 'Je suis une locomotive immobile, figée dans la pierre et l\'architecture.',
            ],
            3 => [
                'name' => 'Étape 3 : Musée Louvre-Lens',
                'qr_map' => 'MAP_LOC_3',
                'qr_mj' => 'MJ_STEP_3',
                'code_part' => '1',
                'riddle' => 'Aux reflets de verre et d\'aluminium se cachent les trésors de l\'histoire.',
            ],
            4 => [
                'name' => 'Étape 4 : Stade Bollaert-Delelis',
                'qr_map' => 'MAP_LOC_4',
                'qr_mj' => 'MJ_STEP_4',
                'code_part' => '9',
                'riddle' => 'Là où résonnent les chants Sang et Or au cœur du bassin minier.',
            ],
            5 => [
                'name' => 'Étape 5 : Les Terrils Jumeaux',
                'qr_map' => 'MAP_LOC_5',
                'qr_mj' => 'MJ_STEP_5',
                'code_part' => 'Office de Tourisme',
                'riddle' => 'Au pied des géants noirs qui dominent le paysage d\'autrefois.',
            ],
            6 => [
                'name' => 'Étape 6 : Église Saint-Léger',
                'qr_map' => 'MAP_LOC_6',
                'qr_mj' => 'MJ_STEP_6',
                'code_part' => 'Coffre Asmodee',
                'riddle' => 'Devant le grand portail gravé se trouve la clé du mystère final.',
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

        // Verify cheat sheet summary and final combination
        $response->assertSee('Récapitulatif des codes');
        $response->assertSee('Code Secret Final (coffre) :');
        $response->assertSee('4819');
    }

    public function test_test_qr_view_auto_seeds_when_steps_table_is_empty(): void
    {
        // Database is fresh and empty (no seed run before request)
        $this->assertEquals(0, Step::count());

        $response = $this->get('/test-qr');

        $response->assertStatus(200);
        $this->assertEquals(6, Step::count());
        $response->assertSee('Étape 1 : Place Jean Jaurès');
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
        $this->assertEquals('#d97706', $json['theme_color']);
        $this->assertEquals('/', $json['start_url']);
        $this->assertNotEmpty($json['icons']);
        $this->assertEquals('/icon.svg', $json['icons'][0]['src']);
    }

    public function test_app_layout_registers_pwa_manifest_and_ios_meta_tags(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('<link rel="manifest" href="/manifest.webmanifest">', false);
        $response->assertSee('<meta name="theme-color" content="#d97706">', false);
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
