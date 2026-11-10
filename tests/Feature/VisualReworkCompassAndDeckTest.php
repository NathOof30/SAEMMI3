<?php

namespace Tests\Feature;

use App\Livewire\Auth\LoginRegister;
use App\Livewire\Game\Hub;
use App\Livewire\Game\Inventory;
use App\Livewire\Game\Scanner;
use App\Livewire\GameRouter;
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
            // 3-step interactive tutorial slider
            ->assertSee('L\'Explorateur', false)
            ->assertSee('L\'enfant résout les énigmes sur la carte papier physique avec son filtre optique.', false)
            ->assertSee('Le Maître du Jeu', false)
            ->assertSee('Le parent s\'oriente en ville grâce à la boussole et valide les étapes.', false)
            ->assertSee('Le Trésor')
            ->assertSee('Trouvez les 6 fragments et remportez le jeu Dobble exclusif à l\'Office de Tourisme !', false)
            // Slider controls
            ->assertSeeHtml('x-on:click="prevSlide()"')
            ->assertSeeHtml('x-on:click="nextSlide()"')
            ->assertSeeHtml('goToSlide(0)')
            ->assertSeeHtml('goToSlide(1)')
            ->assertSeeHtml('goToSlide(2)')
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
            // Deck of cards (-5deg / +5deg with top: 14px)
            ->assertSeeHtml('rotate(-5deg)')
            ->assertSeeHtml('rotate(5deg)')
            ->assertSeeHtml('top: 14px')
            ->assertSeeHtml('id="hub-distance-metric-btn"');

        $html = $test->html();
        $this->assertDoesNotMatch($html, '/[\x{1F300}-\x{1F9FF}]/u');
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

    protected function assertDoesNotMatch(string $content, string $pattern): void
    {
        $this->assertEquals(0, preg_match($pattern, $content), "Content contains forbidden character matching {$pattern}");
    }
}
