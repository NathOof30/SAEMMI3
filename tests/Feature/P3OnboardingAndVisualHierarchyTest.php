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
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class P3OnboardingAndVisualHierarchyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StepSeeder::class);
    }

    public function test_t3_1_begin_screens_have_airy_vertical_margins_and_clear_departure_narrative(): void
    {
        $component = Livewire::test(LoginRegister::class);
        $html = $component->html();

        $component
            ->assertSeeHtml('padding: clamp(18px, 4.2dvh, 42px) 18px clamp(16px, 3.2dvh, 32px) 18px;')
            ->assertSeeHtml('gap: clamp(10px, 2dvh, 22px);')
            ->assertSeeHtml('class="alpine-flex"')
            ->assertSeeHtml('id="partner-logos-header"')
            ->assertSeeHtml('lens-tourisme-logo-e1673958887436.webp')
            ->assertSeeHtml('asmodee_logo.webp')
            ->assertDontSee('Lens Tourisme & Asmodee', false)
            ->assertSee("la base brisée d'une clé antique", false)
            ->assertSee('6 fragments manquants', false)
            ->assertSee("impossible d'ouvrir le coffre secret ni de rallumer la Lampe Cœur", false)
            ->assertSee('Partez en ville avec votre kit retrouver les', false)
            ->assertSee('6 fragments de clé', false)
            ->assertSee('Le premier indice vous attend !', false);

        // Register & login modes preserve consistent vertical margins and scroll container
        $component->call('setMode', 'register')
            ->assertSeeHtml('margin: 12px 0; overflow-y: auto')
            ->assertSeeHtml('box-shadow: 0 6px 0 #1a1a1a');

        $component->call('setMode', 'login')
            ->assertSeeHtml('margin: 12px 0; overflow-y: auto')
            ->assertSeeHtml('box-shadow: 0 6px 0 #1a1a1a');

        // Confirm no graphical emojis in onboarding HTML
        $this->assertEquals(0, preg_match('/[\x{1F300}-\x{1F9FF}]/u', $html));
    }

    public function test_t3_2_shared_five_step_tutorial_explains_kit_game_loop_without_mj_and_syncs_begin_and_hub(): void
    {
        $tutorialPartial = file_get_contents(resource_path('views/components/tutorial-slides.blade.php'));
        $this->assertNotFalse($tutorialPartial);
        $this->assertStringNotContainsString('Maître du Jeu', $tutorialPartial);
        $this->assertDoesNotMatchRegularExpression('/\bMJ\b/u', $tutorialPartial);
        $this->assertStringNotContainsString('Dobble', $tutorialPartial);
        $this->assertStringNotContainsString('Asmodee', $tutorialPartial);
        $this->assertStringNotContainsString('4819', $tutorialPartial);
        $this->assertStringNotContainsString('font-family: system-ui', $tutorialPartial);
        $this->assertEquals(0, preg_match('/[\x{1F300}-\x{1F9FF}]/u', $tutorialPartial));

        $authView = file_get_contents(resource_path('views/livewire/auth/login-register.blade.php'));
        $hubView = file_get_contents(resource_path('views/livewire/game/hub.blade.php'));
        $this->assertNotFalse($authView);
        $this->assertNotFalse($hubView);
        $this->assertStringContainsString('<x-tutorial-slides slide-var="activeSlide"', $authView);
        $this->assertStringContainsString('<x-tutorial-slides slide-var="helpTutorialSlide" :anti-spoil="true"', $hubView);

        // LoginRegister onboarding tutorial shows all 5 complete slides including Office de Tourisme & automatic raffle
        $authHtml = Livewire::test(LoginRegister::class)->html();
        foreach ([
            'Étape 1 sur 5',
            'Votre matériel : Appli + Kit',
            'kit papier',
            'carte de Lens',
            'loupe',
            'Étape 2 sur 5',
            'Chercher &amp; Scanner',
            'scannez le QR code',
            'Étape 3 sur 5',
            "Marcher &amp; Relever l'épreuve",
            'boussole',
            'JAUNE OR',
            "l'animateur de l'étape",
            'petit jeu en famille',
            'Étape 4 sur 5',
            'La Clé Complète',
            'fragment de clé',
            'clé complète',
            'Étape 5 sur 5',
            'Un Véritable Trésor &amp; Tirage au Sort',
            'véritable trésor surprise',
            'automatiquement inscrit au grand tirage au sort',
            'Office de Tourisme',
        ] as $expected) {
            $this->assertStringContainsString($expected, $authHtml);
        }
        $this->assertStringNotContainsString('Maître du Jeu', $authHtml);
        $this->assertStringNotContainsString('Dobble', $authHtml);
        $this->assertStringNotContainsString('4819', $authHtml);
        $this->assertDoesNotMatchRegularExpression('/\bMJ\b/u', $authHtml);

        // Hub tutorial uses the exact same shared 5 slides while avoiding Step 6 destination spoiler
        $user = User::factory()->create(['current_step' => 1, 'phase' => 'GUESSING_LOCATION']);
        $this->actingAs($user);

        $hubHtml = Livewire::test(Hub::class)->html();
        foreach ([
            'Tutoriel en 5 étapes',
            'Relancer le tutoriel (5 étapes)',
            'Étape 1 sur 5',
            'Votre matériel : Appli + Kit',
            'Étape 2 sur 5',
            'Étape 3 sur 5',
            "l'animateur de l'étape",
            'petit jeu en famille',
            'Étape 4 sur 5',
            'La Clé Complète',
            'Étape 5 sur 5',
            'Un Véritable Trésor &amp; Tirage au Sort',
            "au point d'accueil final",
        ] as $expected) {
            $this->assertStringContainsString($expected, $hubHtml);
        }
        $this->assertStringNotContainsString('Dobble', $hubHtml);
        $this->assertStringNotContainsString('Asmodee', $hubHtml);
        $this->assertStringNotContainsString('4819', $hubHtml);
        foreach (Step::all() as $step) {
            $this->assertStringNotContainsString($step->cleanName(), $hubHtml);
        }
    }

    public function test_t3_3_box_shadow_hierarchy_keeps_parent_and_ctas_while_flattening_nested_boxes(): void
    {
        $user = User::factory()->create([
            'current_step' => 3,
            'phase' => 'WALKING_TO_TARGET',
        ]);
        Fragment::create(['user_id' => $user->id, 'step_id' => 1]);
        Fragment::create(['user_id' => $user->id, 'step_id' => 2]);
        $this->actingAs($user);

        // 1. Inventory in-progress: key assembly board, starter badge, and all 6 fragment cards (unlocked & locked) have NO box-shadow
        $invInProgressHtml = Livewire::test(Inventory::class)->html();
        $invXpath = $this->makeXpath($invInProgressHtml);

        $keyAssembly = $invXpath->query('//*[@id="gali-key-assembly"]')->item(0);
        $this->assertInstanceOf(DOMElement::class, $keyAssembly);
        $this->assertStringNotContainsString('box-shadow', $keyAssembly->getAttribute('style'));

        $starterSlot = $invXpath->query('//*[@id="key-fragment-starter"]')->item(0);
        $this->assertInstanceOf(DOMElement::class, $starterSlot);
        $this->assertStringNotContainsString('box-shadow', $starterSlot->getAttribute('style'));

        for ($i = 1; $i <= 6; $i++) {
            $piece = $invXpath->query("//*[@id='key-fragment-{$i}']")->item(0);
            $this->assertInstanceOf(DOMElement::class, $piece, "Missing key fragment slot {$i}");
            $this->assertStringNotContainsString('box-shadow', $piece->getAttribute('style'), "Fragment {$i} should not carry box-shadow");
        }

        // Memory modal inner story/heritage boxes have NO box-shadow while modal card keeps 0 8px 0 and close CTA keeps 0 3px 0
        $memoryModalCard = $invXpath->query('//*[@id="inventory-memory-modal"]/div')->item(0);
        $this->assertInstanceOf(DOMElement::class, $memoryModalCard);
        $this->assertStringContainsString('box-shadow: 0 8px 0 #1A1A1A', $memoryModalCard->getAttribute('style'));

        $memorySubBoxes = $invXpath->query('//*[@id="inventory-memory-modal"]//div[contains(@style, "#FFFBEB") or contains(@style, "#F4EBD9")]');
        $this->assertGreaterThanOrEqual(2, $memorySubBoxes->length);
        foreach ($memorySubBoxes as $box) {
            if ($box instanceof DOMElement) {
                $this->assertStringNotContainsString('box-shadow', $box->getAttribute('style'));
            }
        }

        // 2. Hub (Phase B): fan cards behind Phase B card have NO box-shadow, drawer cards have NO box-shadow
        $hubHtml = Livewire::test(Hub::class)->html();
        $hubXpath = $this->makeXpath($hubHtml);

        $fanCards = $hubXpath->query('//*[@id="phase-b-deck-wrapper"]/div[contains(@style, "rotate(")]');
        $this->assertEquals(2, $fanCards->length);
        foreach ($fanCards as $fanCard) {
            if ($fanCard instanceof DOMElement) {
                $this->assertStringNotContainsString('box-shadow', $fanCard->getAttribute('style'));
            }
        }

        $drawerCards = $hubXpath->query('//*[@id="phase-b-story-scroll"]/div');
        $this->assertEquals(2, $drawerCards->length);
        foreach ($drawerCards as $drawerCard) {
            if ($drawerCard instanceof DOMElement) {
                $this->assertStringNotContainsString('box-shadow', $drawerCard->getAttribute('style'));
            }
        }

        // 3. GameRouter: avatar circle inside header has NO box-shadow while backpack button keeps its button shadow
        $routerHtml = Livewire::test(GameRouter::class)->html();
        $routerXpath = $this->makeXpath($routerHtml);

        $avatarCircle = $routerXpath->query('//button[@title="Profil et équipe"]/div')->item(0);
        $this->assertInstanceOf(DOMElement::class, $avatarCircle);
        $this->assertStringNotContainsString('box-shadow', $avatarCircle->getAttribute('style'));

        $backpackBtn = $routerXpath->query('//button[@title="Ouvrir l\'inventaire"]')->item(0);
        $this->assertInstanceOf(DOMElement::class, $backpackBtn);
        $this->assertStringContainsString('box-shadow: 0 3px 0 #1A1A1A', $backpackBtn->getAttribute('style'));

        // 4. Inventory victory: key assembly, fragment slots, treasure QR card/frame, and automatic raffle card have NO box-shadow (no 4819 padlock)
        for ($i = 3; $i <= 6; $i++) {
            Fragment::create(['user_id' => $user->id, 'step_id' => $i]);
        }
        $invVictoryHtml = Livewire::test(Inventory::class)->html();
        $victoryXpath = $this->makeXpath($invVictoryHtml);

        $victoryKeyAssembly = $victoryXpath->query('//*[@id="gali-key-assembly"]')->item(0);
        $this->assertInstanceOf(DOMElement::class, $victoryKeyAssembly);
        $this->assertStringNotContainsString('box-shadow', $victoryKeyAssembly->getAttribute('style'));

        for ($i = 1; $i <= 6; $i++) {
            $piece = $victoryXpath->query("//*[@id='key-fragment-{$i}']")->item(0);
            $this->assertInstanceOf(DOMElement::class, $piece);
            $this->assertStringNotContainsString('box-shadow', $piece->getAttribute('style'));
        }

        $qrCard = $victoryXpath->query('//*[@id="victory-treasure-qr-card"]')->item(0);
        $this->assertInstanceOf(DOMElement::class, $qrCard);
        $this->assertStringNotContainsString('box-shadow', $qrCard->getAttribute('style'));

        $qrFrame = $victoryXpath->query('//*[@id="reward-qr-payload"]')->item(0);
        $this->assertInstanceOf(DOMElement::class, $qrFrame);
        $this->assertStringNotContainsString('box-shadow', $qrFrame->getAttribute('style'));

        $raffleCard = $victoryXpath->query('//*[@id="victory-raffle-card"]')->item(0);
        $this->assertInstanceOf(DOMElement::class, $raffleCard);
        $this->assertStringNotContainsString('box-shadow', $raffleCard->getAttribute('style'));

        $this->assertStringNotContainsString('Code final :', $invVictoryHtml);
        $this->assertStringNotContainsString('4819', $invVictoryHtml);
    }

    public function test_t3_4_scanner_notification_overlay_removes_hard_black_shadow_and_ring_to_keep_clean_glow(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);
        $this->actingAs($user);

        // Success overlay
        $successHtml = Livewire::test(Scanner::class)
            ->call('verifyCode', 'MAP_LOC_1')
            ->assertSet('scanStatus', 'success')
            ->html();

        $this->assertStringContainsString('scan-card-glow-success', $successHtml);
        $this->assertStringContainsString('box-shadow: 0 0 38px rgba(34, 197, 94, 0.55)', $successHtml);
        $this->assertStringNotContainsString('0 10px 0 #1A1A1A', $successHtml);
        $this->assertStringNotContainsString('0 0 0 8px rgba', $successHtml);

        // Error overlay
        $errorHtml = Livewire::test(Scanner::class)
            ->call('verifyCode', 'UNKNOWN_CODE')
            ->assertSet('scanStatus', 'error')
            ->html();

        $this->assertStringContainsString('scan-card-glow-error', $errorHtml);
        $this->assertStringContainsString('box-shadow: 0 0 38px rgba(239, 68, 68, 0.55)', $errorHtml);
        $this->assertStringNotContainsString('0 10px 0 #1A1A1A', $errorHtml);
        $this->assertStringNotContainsString('0 0 0 8px rgba', $errorHtml);
    }

    public function test_t3_5_and_t3_6_memory_modal_has_single_close_button_and_help_modal_has_generous_card_spacing(): void
    {
        $user = User::factory()->create([
            'current_step' => 2,
            'phase' => 'GUESSING_LOCATION',
        ]);
        Fragment::create(['user_id' => $user->id, 'step_id' => 1]);
        $this->actingAs($user);

        // [T3-5] Inventory memory modal: only bottom 'Fermer la fiche' button, no top '✕' button
        $invHtml = Livewire::test(Inventory::class)->html();
        $invXpath = $this->makeXpath($invHtml);

        $closeBtns = $invXpath->query('//*[@id="inventory-memory-modal"]//button[@id="close-memory-modal-btn"]');
        $this->assertEquals(1, $closeBtns->length);
        $closeBtn = $closeBtns->item(0);
        $this->assertInstanceOf(DOMElement::class, $closeBtn);
        $this->assertSame('Fermer la fiche', trim($closeBtn->textContent));

        $allModalButtons = $invXpath->query('//*[@id="inventory-memory-modal"]//button');
        $this->assertEquals(1, $allModalButtons->length);
        $this->assertStringNotContainsString('✕', $invXpath->query('//*[@id="inventory-memory-modal"]')->item(0)?->textContent ?? '');

        // [T3-5] & [T3-6] Help modal: generous gap (14px), flex-shrink: 0, no child box-shadows, no 3 stacked exit actions
        $hubHtml = Livewire::test(Hub::class)->html();
        $hubXpath = $this->makeXpath($hubHtml);

        $guideContainer = $hubXpath->query('//*[@id="hub-help-modal"]//div[@id="hub-help-guide-scroll"]')->item(0);
        $this->assertInstanceOf(DOMElement::class, $guideContainer);
        $this->assertStringContainsString('alpine-flex', $guideContainer->getAttribute('class'));
        $this->assertStringContainsString('gap: 14px', $guideContainer->getAttribute('style'));
        $this->assertStringContainsString('padding: 6px 4px 10px 2px', $guideContainer->getAttribute('style'));

        $layoutBlade = file_get_contents(resource_path('views/components/layouts/app.blade.php'));
        $this->assertNotFalse($layoutBlade);
        $this->assertStringContainsString('.alpine-flex', $layoutBlade);

        $guideCards = $hubXpath->query('./div', $guideContainer);
        $this->assertEquals(3, $guideCards->length);
        foreach ($guideCards as $card) {
            if ($card instanceof DOMElement) {
                $style = $card->getAttribute('style');
                $this->assertStringContainsString('flex-shrink: 0', $style);
                $this->assertStringNotContainsString('box-shadow', $style);
            }
        }

        $helpModalText = $hubXpath->query('//*[@id="hub-help-modal"]')->item(0)?->textContent ?? '';
        $this->assertStringNotContainsString('Revenir au jeu', $helpModalText);
    }

    public function test_t3_7_vertical_broken_key_svg_and_clean_wording_across_game_views(): void
    {
        $user = User::factory()->create([
            'current_step' => 1,
            'phase' => 'WALKING_TO_TARGET',
            'team_name' => 'Les Explorateurs',
        ]);
        $this->actingAs($user);

        // 0/6 state: grey vertical key SVG with starter attic crown in gold, 6 broken locked shards, no bottom continue CTA
        $invZeroHtml = Livewire::test(Inventory::class)->html();
        $invZeroXpath = $this->makeXpath($invZeroHtml);

        $svg = $invZeroXpath->query('//*[@id="gali-vertical-key-svg"]')->item(0);
        $this->assertInstanceOf(DOMElement::class, $svg);
        $this->assertSame('0 0 130 350', $svg->getAttribute('viewBox'));

        $starterGoldPath = $invZeroXpath->query('//*[@id="key-svg-shard-starter"]/path[1]')->item(0);
        $this->assertInstanceOf(DOMElement::class, $starterGoldPath);
        $this->assertSame('#F4B316', $starterGoldPath->getAttribute('fill'));

        $this->assertStringContainsString('Déjà acquis  |  Grenier', $invZeroHtml);
        $this->assertEquals(0, $invZeroXpath->query('//*[@id="inventory-continue-next-btn"]')->length);

        // Scanner phase B completion redirects to inventory and dispatches fragment-unlocked
        Livewire::test(Scanner::class)
            ->call('verifyCode', 'MJ_STEP_1')
            ->assertSet('scanTargetTab', 'inventory')
            ->assertDispatched('fragment-unlocked', stepId: 1)
            ->assertSeeHtml('Placer le fragment sur la clé →');

        // 1/6 state: shard 1 becomes clickable gold, bottom continue CTA appears
        $invOneHtml = Livewire::test(Inventory::class)->html();
        $invOneXpath = $this->makeXpath($invOneHtml);

        $unlockedSvgShard = $invOneXpath->query('//*[@id="key-svg-shard-1"]')->item(0);
        $this->assertInstanceOf(DOMElement::class, $unlockedSvgShard);
        $this->assertStringContainsString('openMemory(1)', $unlockedSvgShard->getAttribute('x-on:click'));
        $unlockedSvgPath = $invOneXpath->query('//*[@id="key-svg-shard-1"]/path[1]')->item(0);
        $this->assertInstanceOf(DOMElement::class, $unlockedSvgPath);
        $this->assertSame('#F4B316', $unlockedSvgPath->getAttribute('fill'));

        $this->assertEquals(1, $invOneXpath->query('//*[@id="inventory-continue-next-btn"]')->length);
        $this->assertStringContainsString("Bord de l'anneau", $invOneHtml);

        // 6/6 victory state: secret treasure QR + automatic raffle without checkbox, zero Dobble/4819/Asmodee
        for ($i = 2; $i <= 6; $i++) {
            Fragment::create(['user_id' => $user->id, 'step_id' => $i]);
        }
        $user->update(['current_step' => 7, 'phase' => 'GUESSING_LOCATION']);

        $invVictoryHtml = Livewire::test(Inventory::class)->html();
        $this->assertStringContainsString('Clé Complète assemblée (6/6)', $invVictoryHtml);
        $this->assertStringContainsString('QR Code Retrait du Trésor', $invVictoryHtml);
        $this->assertStringContainsString('id="victory-raffle-card"', $invVictoryHtml);
        $this->assertStringContainsString('automatiquement inscrite', $invVictoryHtml);
        $this->assertStringNotContainsString('type="checkbox"', $invVictoryHtml);

        $layoutBlade = file_get_contents(resource_path('views/components/layouts/app.blade.php'));
        $this->assertNotFalse($layoutBlade);
        $this->assertStringContainsString('aria-label="QR Code Retrait du Trésor"', $layoutBlade);
        $this->assertStringNotContainsString('Lot Dobble', $layoutBlade);

        // Enforce zero em-dashes, Dobble, 4819, Asmodee, or Maître du Jeu across player-facing livewire views
        $viewsToCheck = [
            Livewire::test(GameRouter::class)->html(),
            Livewire::test(Hub::class)->html(),
            Livewire::test(Scanner::class)->html(),
            $invOneHtml,
            $invVictoryHtml,
        ];
        foreach ($viewsToCheck as $renderedHtml) {
            $this->assertStringNotContainsString('—', $renderedHtml);
            $this->assertStringNotContainsString('–', $renderedHtml);
            $this->assertStringNotContainsString('Dobble', $renderedHtml);
            $this->assertStringNotContainsString('4819', $renderedHtml);
            $this->assertStringNotContainsString('Asmodee', $renderedHtml);
            $this->assertStringNotContainsString('Maître du Jeu', $renderedHtml);
        }
    }

    private function makeXpath(string $html): DOMXPath
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        libxml_clear_errors();

        return new DOMXPath($dom);
    }
}
