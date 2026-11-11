<?php

namespace App\Livewire\Game;

use App\Models\Fragment;
use App\Models\Step;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Scanner extends Component
{
    public string $manualCode = '';

    public ?string $message = null;

    public ?string $error = null;

    public ?string $scanStatus = null;

    public ?string $scanTitle = null;

    public ?string $scanSubtitle = null;

    public ?string $scanTargetTab = null;

    public function resetScanState(): void
    {
        $this->error = null;
        $this->message = null;
        $this->manualCode = '';
        $this->scanStatus = null;
        $this->scanTitle = null;
        $this->scanSubtitle = null;
        $this->scanTargetTab = null;
    }

    public function dismissScanOverlay(): void
    {
        $target = $this->scanTargetTab;
        $this->resetScanState();
        if ($target !== null) {
            $this->dispatch('set-tab', tab: $target);
        }
    }

    public function verifyManualCode(): void
    {
        $this->verifyCode($this->manualCode);
    }

    private function applyScanFeedback(string $status, string $title, string $subtitle, ?string $targetTab = null): void
    {
        $this->scanStatus = $status;
        $this->scanTitle = $title;
        $this->scanSubtitle = $subtitle;
        $this->scanTargetTab = $targetTab;
        $this->dispatch('scan-feedback', status: $status);
    }

    public function verifyCode(?string $code = null): void
    {
        $code = trim((string) ($code !== null && $code !== '' ? $code : $this->manualCode));
        $this->resetScanState();

        if ($code === '') {
            $this->error = 'Veuillez saisir ou scanner un code.';
            $this->applyScanFeedback('error', 'Code manquant', 'Veuillez scanner ou saisir un QR code valide.');

            return;
        }

        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            $this->error = 'Utilisateur non connecté.';
            $this->applyScanFeedback('error', 'Erreur d\'accès', 'Vous devez être connecté pour participer à l\'expédition.');

            return;
        }

        if ($user->fragments()->count() >= 6 || $user->current_step > 6) {
            $this->message = 'Toutes les étapes ont déjà été validées ! Félicitations !';
            $this->applyScanFeedback(
                'success',
                'Expédition Déjà Terminée !',
                'Tous les fragments de clé ont déjà été réunis. Consultez votre inventaire.',
                'inventory'
            );

            return;
        }

        $currentStep = Step::find($user->current_step);

        if (! $currentStep) {
            $this->error = 'Étape introuvable.';
            $this->applyScanFeedback('error', 'Étape Introuvable', 'Impossible de trouver les données de cette étape.');

            return;
        }

        $upperCode = strtoupper($code);
        $expectedMapCode = strtoupper($currentStep->qr_map_code);
        $expectedMjCode = strtoupper($currentStep->qr_mj_code);
        $resolved = Step::resolveFromQrCode($code);

        if ($user->phase === 'GUESSING_LOCATION') {
            $isMapMatch = ($upperCode === $expectedMapCode)
                || ($resolved['type'] === 'map' && $resolved['step_number'] === $currentStep->id);

            if ($isMapMatch) {
                $user->phase = 'WALKING_TO_TARGET';
                $user->save();

                $cleanStepName = $currentStep->cleanName();

                $this->message = "QR Map validé ! Direction la cible pour l'étape {$user->current_step} : {$currentStep->name}.";
                $this->applyScanFeedback(
                    'success',
                    'Bravo ! Emplacement trouvé !',
                    "Cap activé vers {$cleanStepName} ! Suivez l'aiguille de la boussole.",
                    'hub'
                );
                $this->dispatch('step-updated');

                return;
            }

            $scannedStep = $resolved['step'];
            $scannedNumber = $resolved['step_number'] ?? $scannedStep?->id;

            if ($resolved['type'] === 'map') {
                if ($scannedStep && $scannedNumber !== null && $scannedNumber < $currentStep->id) {
                    $title = 'Déjà exploré !';
                    $subtitle = "Tu es déjà passé par {$scannedStep->cleanName()} ! Observe bien l'indice de l'étape {$currentStep->id} avec ta loupe pour trouver la suite.";
                } elseif ($scannedStep && $scannedStep->id !== $currentStep->id) {
                    $title = 'Mince ! Je ne crois pas que ça soit...';
                    $subtitle = $scannedStep->buildWrongMapFeedback($currentStep);
                } else {
                    $title = 'Mince ! Je ne crois pas que ça soit...';
                    $subtitle = "Ce repère ne correspond pas à l'énigme de l'étape {$currentStep->id}. Observez bien l'indice de Gali avec la loupe et cherchez un autre symbole sur la carte !";
                }
            } elseif ($resolved['type'] === 'mj') {
                if ($scannedStep && $scannedNumber !== null && $scannedNumber < $currentStep->id) {
                    $title = 'Pas si vite, explorateur !';
                    $subtitle = "Ce badge d'épreuve correspond à {$scannedStep->cleanName()}, que tu as déjà réussi ! Trouve d'abord la prochaine destination sur la carte papier avec la loupe.";
                } else {
                    $title = ($scannedNumber !== null && $scannedNumber > $currentStep->id)
                        ? 'Holà, téléportation détectée !'
                        : 'Pas si vite, explorateur !';
                    $subtitle = "Holà, téléportation détectée ! Ce badge de l'animateur sur place ne peut pas encore être devant toi… Trouve d'abord la destination sur la carte papier avec la loupe !";
                }
            } else {
                $title = 'Mince ! Mauvais QR Code...';
                $subtitle = "Ce QR code inconnu ne fait pas partie du carnet de Gali ! Scanne un repère imprimé sur la carte papier pour l'étape {$currentStep->id}.";
            }

            $this->error = "Code invalide pour l'étape actuelle. Vous devez scanner le QR code de la carte (MAP_LOC_{$currentStep->id}).";
            $this->applyScanFeedback('error', $title, $subtitle);

            return;
        }

        if ($user->phase === 'WALKING_TO_TARGET') {
            $isMjMatch = ($upperCode === $expectedMjCode)
                || ($resolved['type'] === 'mj' && $resolved['step_number'] === $currentStep->id);

            if ($isMjMatch) {
                Fragment::firstOrCreate([
                    'user_id' => $user->id,
                    'step_id' => $currentStep->id,
                ]);

                $user->current_step += 1;
                $user->phase = 'GUESSING_LOCATION';
                $user->save();

                $this->dispatch('step-updated');
                $this->dispatch('fragment-unlocked', stepId: $currentStep->id);

                if ($user->current_step > 6 || $user->fragments()->count() >= 6) {
                    $this->message = "Badge validé ! Dernier fragment obtenu pour l'étape {$currentStep->id}. Vous avez réuni les 6 fragments ! Rendez-vous dans l'Inventaire pour voir la victoire !";
                    $this->applyScanFeedback(
                        'success',
                        'Victoire Absolue !',
                        'Les 6 fragments de la clé sont réunis ! Rendez-vous dans votre inventaire pour assembler la clé complète, rallumer la Lampe Cœur et découvrir votre trésor secret.',
                        'inventory'
                    );
                } else {
                    $this->message = "Badge validé ! Fragment de clé obtenu pour l'étape {$currentStep->id}.";
                    $this->applyScanFeedback(
                        'success',
                        'Bravo ! Épreuve réussie !',
                        "Fragment de clé {$currentStep->id}/6 débloqué ({$currentStep->code_part}) ! Placez-le sur votre clé dans l'inventaire.",
                        'inventory'
                    );
                }

                return;
            }

            $scannedStep = $resolved['step'];
            $scannedNumber = $resolved['step_number'] ?? $scannedStep?->id;
            $currentCleanName = $currentStep->cleanName();

            if ($resolved['type'] === 'mj') {
                if ($scannedStep && $scannedNumber !== null && $scannedNumber < $currentStep->id) {
                    $title = 'Mince ! Mauvais badge d\'étape...';
                    $subtitle = "Ce badge appartient à {$scannedStep->cleanName()}, une étape déjà validée ! Rejoins plutôt {$currentCleanName} pour rencontrer le bon animateur sur place.";
                } elseif ($scannedNumber !== null && $scannedNumber > $currentStep->id) {
                    $title = 'Holà, téléportation détectée !';
                    $subtitle = "Ce badge appartient à une étape que tu n'as pas encore débloquée ! Rejoins d'abord {$currentCleanName} pour valider l'étape {$currentStep->id}.";
                } else {
                    $title = 'Mince ! Mauvais badge d\'étape...';
                    $subtitle = "Ce badge ne correspond pas à l'animateur sur place de {$currentCleanName} ! Scanne le badge officiel sur place pour valider l'étape {$currentStep->id}.";
                }
            } elseif ($resolved['type'] === 'map') {
                $title = 'Emplacement déjà validé !';
                if ($scannedNumber === $currentStep->id) {
                    $subtitle = "Tu as déjà localisé {$currentCleanName} sur la carte ! Rends-toi sur place et scanne le badge de l'animateur sur place.";
                } elseif ($scannedStep && $scannedNumber !== null && $scannedNumber < $currentStep->id) {
                    $subtitle = "Tu es déjà passé par {$scannedStep->cleanName()} ! Suis la boussole vers {$currentCleanName} pour scanner le badge de l'animateur sur place.";
                } else {
                    $subtitle = "L'emplacement actuel ({$currentCleanName}) est déjà validé, et ce repère appartient à une autre étape ! Scanne d'abord le badge de l'animateur sur place.";
                }
            } else {
                $title = 'Mince ! Mauvais QR Code...';
                $subtitle = "Ce QR code inconnu ne fait pas partie du carnet de Gali ! Rejoins {$currentCleanName} et scanne le badge officiel de l'animateur sur place.";
            }

            $this->error = "Code invalide pour l'étape actuelle. Vous devez scanner le QR code de l'animateur sur place (MJ_STEP_{$currentStep->id}).";
            $this->applyScanFeedback('error', $title, $subtitle);

            return;
        }

        $this->error = 'Phase de jeu inconnue.';
        $this->applyScanFeedback('error', 'Phase Inconnue', 'Impossible de déterminer la phase de jeu.');
    }

    public function render()
    {
        return view('livewire.game.scanner', [
            'user' => Auth::user(),
        ]);
    }
}
