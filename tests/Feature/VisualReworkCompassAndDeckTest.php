<?php

namespace Tests\Feature;

use App\Livewire\Auth\LoginRegister;
use App\Livewire\Game\Hub;
use App\Livewire\Game\Inventory;
use App\Livewire\Game\Scanner;
use App\Livewire\GameRouter;
use App\Models\Fragment;
use App\Models\Step;
use App\Models\User;
use Database\Seeders\StepSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VisualReworkCompassAndDeckTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StepSeeder::class);
    }

    public function test_global_layout_applies_seamless_dotted_background_and_no_emojis(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Seamless dotted background pattern & full viewport containment
        $response->assertSee('radial-gradient(#D1D5DB 1.5px, transparent 1.5px)', false);
        $response->assertSee('background-size: 20px 20px', false);
        $response->assertSee('#FAF8F5');
        $response->assertSee('height: 100dvh;', false);
        $response->assertSee('overflow: hidden;', false);
        $response->assertSee('overscroll-behavior: none;', false);

        // Absolute absence of emojis in the rendered page
        $content = $response->getContent();
        $this->assertDoesNotMatch($content, '/[\x{1F300}-\x{1F9FF}]/u');
        $this->assertStringNotContainsString('🗺️', $content);
        $this->assertStringNotContainsString('🧭', $content);
        $this->assertStringNotContainsString('🏆', $content);
        $this->assertStringNotContainsString('⚙️', $content);
        $this->assertStringNotContainsString('⏳', $content);
        $this->assertStringNotContainsString('🔒', $content);
    }

    public function test_onboarding_and_tutorial_slider_without_emojis_and_no_verbose_warning(): void
    {
        $test = Livewire::test(LoginRegister::class);

        // Minimalist landing
        $test->assertSee('L\'Expédition Secrète', false)
            ->assertSee('Lens Tourisme & Asmodee', false)
            ->assertSee('Démarrer l\'aventure', false)
            ->assertDontSee('Application réservée au Parent', false)
            // 4-step interactive tutorial slider
            ->assertSee('Votre matériel : Appli + Kit', false)
            ->assertSee('kit papier', false)
            ->assertDontSee('filtre optique', false)
            ->assertDontSee('Galibrio', false)
            ->assertDontSee('Maître du Jeu', false)
            ->assertSee('Chercher & Scanner', false)
            ->assertSee('Marcher & Relever l\'épreuve', false)
            ->assertSee('l\'animateur de l\'étape', false)
            ->assertSee('petit jeu en famille', false)
            ->assertSee('La Carte & Le Coffre final', false)
            ->assertSee('Ouvrez le vrai coffre à l\'Office de Tourisme et remportez votre jeu Dobble !', false)
            // Slider controls
            ->assertSeeHtml('x-on:click="prevSlide()"')
            ->assertSeeHtml('x-on:click="nextSlide()"')
            ->assertSeeHtml('goToSlide(0)')
            ->assertSeeHtml('goToSlide(1)')
            ->assertSeeHtml('goToSlide(2)')
            ->assertSeeHtml('goToSlide(3)')
            // Perfect round pagination dots
            ->assertSeeHtml('min-width: 14px')
            ->assertSeeHtml('min-height: 14px')
            ->assertSeeHtml('flex-shrink: 0')
            ->assertSeeHtml('border-radius: 50%')
            // Final action buttons
            ->assertSee('Créer une équipe')
            ->assertSee('Connexion équipe')
            ->assertSee('Suivant →');

        $html = $test->html();
        $this->assertDoesNotMatch($html, '/[\x{1F300}-\x{1F9FF}]/u');
    }

    public function test_auth_forms_share_uniform_card_styling_and_vertical_responsiveness(): void
    {
        $test = Livewire::test(LoginRegister::class);

        // Landing uniform card
        $test->assertSeeHtml('border: 3.5px solid #1a1a1a')
            ->assertSeeHtml('border-radius: 20px')
            ->assertSeeHtml('box-shadow: 0 6px 0 #1a1a1a');

        // Register uniform card
        $test->call('setMode', 'register')
            ->assertSeeHtml('margin: 12px 0; overflow-y: auto')
            ->assertSeeHtml('border: 3.5px solid #1a1a1a')
            ->assertSeeHtml('border-radius: 20px')
            ->assertSeeHtml('box-shadow: 0 6px 0 #1a1a1a');

        // Login uniform card
        $test->call('setMode', 'login')
            ->assertSeeHtml('margin: 12px 0; overflow-y: auto')
            ->assertSeeHtml('border: 3.5px solid #1a1a1a')
            ->assertSeeHtml('border-radius: 20px')
            ->assertSeeHtml('box-shadow: 0 6px 0 #1a1a1a');
    }

    public function test_phase_a_mystery_compass_with_swaying_question_mark_and_dial(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        $test = Livewire::test(Hub::class);

        // Dial with 4 cardinal points rotating with -deviceHeading
        $test->assertSeeHtml('id="compass-dial"')
            ->assertSeeHtml(':transform="\'rotate(\' + dialAngle + \' 100 100)\'"')
            ->assertSeeHtml('font-size="16" font-weight="900" fill="#B91C1C">N</text>')
            ->assertSeeHtml('>S</text>')
            ->assertSeeHtml('>O</text>')
            ->assertSeeHtml('>E</text>')
            // Question mark replacing needle in Phase A
            ->assertSeeHtml('id="phase-a-question-mark"')
            ->assertSeeHtml('class="idle-sway"')
            ->assertSeeHtml('fill="#F4B316"')
            ->assertDontSeeHtml('id="compass-needle-target"')
            ->assertDontSeeHtml('DEVANT')
            ->assertDontSee('Direction la cible')
            // Discreet debug button available in Phase A
            ->assertSeeHtml('[•] debug')
            ->assertSeeHtml('x-on:click="debugModalOpen = !debugModalOpen"')
            // Compass responsiveness
            ->assertSeeHtml('max-width: min(70vw, 260px)')
            // Anti-spoil Phase A title without revealing step destination name
            ->assertSeeHtml('Destination : <span style="color: #F4B316;">Mystère (Étape 1/6)</span>')
            ->assertDontSee('Gare de Lens')
            ->assertDontSee('Destination : <span style="color: #F4B316;">Étape 1 :')
            // Responsive card deck with 22px side margins (width: calc(100% - 44px); max-width: 295px)
            ->assertSeeHtml('width: calc(100% - 44px); max-width: 295px')
            // Deck of cards fan shape (-5deg / +5deg) with top: 14px offset and bottom cut-off
            ->assertSeeHtml('rotate(-5deg)')
            ->assertSeeHtml('rotate(5deg)')
            ->assertSeeHtml('top: 14px')
            ->assertSeeHtml('border-bottom: none')
            ->assertSeeHtml('id="phase-a-scanner-btn"')
            ->assertSee('Scanner la carte');

        $html = $test->html();
        $this->assertDoesNotMatch($html, '/[\x{1F300}-\x{1F9FF}]/u');
    }

    public function test_phase_b_compass_needle_differentiation_gold_target_and_red_opposite(): void
    {
        $user = User::factory()->create([
            'current_step' => 2,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($user);

        $test = Livewire::test(Hub::class);

        // Bicolore needle: gold pointer pointing to target, red opposite tail
        $test->assertSeeHtml('id="compass-needle-target"')
            ->assertSeeHtml('fill="#F4B316"') // Gold target tip pointing towards destination
            ->assertSeeHtml('id="compass-needle-opposite"')
            ->assertSeeHtml('fill="#B91C1C"') // Red Sang opposite tail
            // Refined strokes
            ->assertSeeHtml('stroke-width="3.5"')
            ->assertSeeHtml('stroke-width="4"')
            // No DEVANT, no Direction la cible
            ->assertDontSeeHtml('DEVANT')
            ->assertDontSee('Direction la cible')
            // Discreet debug button
            ->assertSeeHtml('[•] debug')
            ->assertSeeHtml('x-on:click="debugModalOpen = !debugModalOpen"')
            // Compass responsiveness
            ->assertSeeHtml('max-width: min(70vw, 260px)')
            // Clean destination title without 'Étape X :'
            ->assertSeeHtml('Destination : <span style="color: #F4B316;">Centre-Ville</span>')
            ->assertDontSee('Destination : <span style="color: #F4B316;">Étape 2 :')
            // Responsive card deck with 22px side margins
            ->assertSeeHtml('width: calc(100% - 44px); max-width: 295px')
            // Deck of cards (-5deg / +5deg with top: 14px)
            ->assertSeeHtml('rotate(-5deg)')
            ->assertSeeHtml('rotate(5deg)')
            ->assertSeeHtml('top: 14px')
            ->assertSeeHtml('id="hub-distance-metric-btn"');

        $html = $test->html();
        $this->assertDoesNotMatch($html, '/[\x{1F300}-\x{1F9FF}]/u');
    }

    public function test_test_qr_route_enables_vertical_scrolling(): void
    {
        $response = $this->get('/test-qr');
        $response->assertStatus(200);
        $response->assertSee('overflow-y: auto !important', false);
        $response->assertSee('height: auto !important', false);
        $response->assertSee('max-height: none !important', false);
        $response->assertSee('overscroll-behavior: auto !important', false);
        $response->assertSee('background-color: #0b1120 !important', false);
    }

    public function test_debug_modal_provides_teleportation_buttons_without_emojis(): void
    {
        $user = User::factory()->create([
            'current_step' => 2,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($user);

        $test = Livewire::test(Hub::class);

        // Direct TP actions
        $test->assertSeeHtml('id="teleport-target-btn"')
            ->assertSee('TP sur le spot (0m)')
            ->assertSeeHtml('teleportToTarget(0)')
            ->assertSeeHtml('id="teleport-approach-btn"')
            ->assertSee('TP approche (100m)')
            ->assertSeeHtml('teleportToTarget(100)');

        $html = $test->html();
        $this->assertDoesNotMatch($html, '/[\x{1F300}-\x{1F9FF}]/u');
    }

    public function test_navigation_removes_bottom_navbar_and_adds_back_button(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Game router has no footer navbar
        Livewire::test(GameRouter::class)
            ->assertDontSeeHtml('<footer')
            ->assertDontSeeHtml('aria-label="Navigation persistante bas d\'écran"');

        // Scanner has return to compass button
        Livewire::test(Scanner::class)
            ->assertSeeHtml('id="scanner-back-to-hub-btn"')
            ->assertSee('← Retour à la boussole');

        // Inventory has return to compass button and no emojis
        $invTest = Livewire::test(Inventory::class)
            ->assertSeeHtml('id="inventory-back-to-hub-btn"')
            ->assertSee('← Retour à la boussole')
            ->assertDontSee('🔒')
            ->assertDontSee('⏳');

        $invHtml = $invTest->html();
        $this->assertDoesNotMatch($invHtml, '/[\x{1F300}-\x{1F9FF}]/u');
    }

    public function test_inventory_cleans_step_name_without_duplicated_step_prefix(): void
    {
        $user = User::factory()->create();
        Fragment::create([
            'user_id' => $user->id,
            'step_id' => 1,
        ]);
        $this->actingAs($user);

        Livewire::test(Inventory::class)
            ->assertSeeHtml('Étape 1 : Gare de Lens — Fragment :')
            ->assertDontSee('Étape 1 : Étape 1 :');
    }

    protected function assertDoesNotMatch(string $content, string $pattern): void
    {
        $this->assertEquals(0, preg_match($pattern, $content), "Content contains forbidden character matching {$pattern}");
    }
}
