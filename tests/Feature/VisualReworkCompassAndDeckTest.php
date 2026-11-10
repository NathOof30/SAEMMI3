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

        // Seamless dotted background pattern
        $response->assertSee('radial-gradient(#D1D5DB 1.5px, transparent 1.5px)', false);
        $response->assertSee('background-size: 20px 20px', false);
        $response->assertSee('#FAF8F5');

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
            // Deck of cards fan shape (-6deg / +6deg) and bottom cut-off
            ->assertSeeHtml('rotate(-6deg)')
            ->assertSeeHtml('rotate(6deg)')
            ->assertSeeHtml('border-bottom: none')
            ->assertSeeHtml('id="phase-a-scanner-btn"')
            ->assertSee('Scanner la carte');

        $html = $test->html();
        $this->assertDoesNotMatch($html, '/[\x{1F300}-\x{1F9FF}]/u');
    }

    public function test_phase_b_compass_needle_differentiation_red_target_and_gold_opposite(): void
    {
        $user = User::factory()->create([
            'current_step' => 2,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        $this->actingAs($user);

        $test = Livewire::test(Hub::class);

        // Bicolore needle
        $test->assertSeeHtml('id="compass-needle-target"')
            ->assertSeeHtml('fill="#B91C1C"') // Red Sang target tip
            ->assertSeeHtml('id="compass-needle-opposite"')
            ->assertSeeHtml('fill="#F4B316"') // Gold opposite tail
            // Refined strokes
            ->assertSeeHtml('stroke-width="3.5"')
            ->assertSeeHtml('stroke-width="4"')
            // No DEVANT, no Direction la cible
            ->assertDontSeeHtml('DEVANT')
            ->assertDontSee('Direction la cible')
            // Discreet debug button
            ->assertSeeHtml('[•] debug')
            ->assertSeeHtml('x-on:click="debugModalOpen = !debugModalOpen"')
            // Deck of cards
            ->assertSeeHtml('rotate(-6deg)')
            ->assertSeeHtml('rotate(6deg)')
            ->assertSeeHtml('id="hub-distance-metric-btn"');

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
