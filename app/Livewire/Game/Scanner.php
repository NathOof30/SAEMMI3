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

    public function verifyCode(?string $code = null): void
    {
        $code = trim((string) ($code !== null && $code !== '' ? $code : $this->manualCode));
        $this->resetScanState();

        if ($code === '') {
            $this->error = 'Veuillez saisir ou scanner un code.';
            $this->scanStatus = 'error';
            $this->scanTitle = 'Code manquant';
            $this->scanSubtitle = 'Veuillez scanner ou saisir un QR code valide.';

            return;
        }

        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            $this->error = 'Utilisateur non connecté.';
            $this->scanStatus = 'error';
            $this->scanTitle = 'Erreur d\'accès';
            $this->scanSubtitle = 'Vous devez être connecté pour participer à l\'expédition.';

            return;
        }

        if ($user->fragments()->count() >= 6 || $user->current_step > 6) {
            $this->message = 'Toutes les étapes ont déjà été validées ! Félicitations !';
            $this->scanStatus = 'success';
            $this->scanTitle = 'Expédition Déjà Terminée !';
            $this->scanSubtitle = 'Tous les fragments ont déjà été réunis. Consultez votre inventaire.';
            $this->scanTargetTab = 'inventory';

            return;
        }

        $currentStep = Step::find($user->current_step);

        if (! $currentStep) {
            $this->error = 'Étape introuvable.';
            $this->scanStatus = 'error';
            $this->scanTitle = 'Étape Introuvable';
            $this->scanSubtitle = 'Impossible de trouver les données de cette étape.';

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
                $this->scanStatus = 'success';
                $this->scanTitle = 'Bravo ! Emplacement trouvé !';
                $this->scanSubtitle = "Cap activé vers {$cleanStepName} ! Suivez l'aiguille de la boussole.";
                $this->scanTargetTab = 'hub';
                $this->dispatch('step-updated');

                return;
            }

            $scannedStep = $resolved['step'];
            $scannedNumber = $resolved['step_number'] ?? $scannedStep?->id;

            if ($resolved['type'] === 'map') {
                if ($scannedStep && $scannedNumber !== null && $scannedNumber < $currentStep->id) {
                    $this->scanTitle = 'Déjà exploré !';
                    $this->scanSubtitle = "Tu es déjà passé par {$scannedStep->cleanName()} ! Observe bien l'indice de l'étape {$currentStep->id} avec ta loupe pour trouver la suite.";
                } elseif ($scannedStep && $scannedStep->id !== $currentStep->id) {
                    $this->scanTitle = 'Mince ! Je ne crois pas que ça soit...';
                    $this->scanSubtitle = $scannedStep->buildWrongMapFeedback($currentStep);
                } else {
                    $this->scanTitle = 'Mince ! Je ne crois pas que ça soit...';
                    $this->scanSubtitle = "Ce repère ne correspond pas à l'énigme de l'étape {$currentStep->id}. Observez bien l'indice de Gali avec la loupe et cherchez un autre symbole sur la carte !";
                }
            } elseif ($resolved['type'] === 'mj') {
                if ($scannedStep && $scannedNumber !== null && $scannedNumber < $currentStep->id) {
                    $this->scanTitle = 'Pas si vite, explorateur !';
                    $this->scanSubtitle = "Ce Maître du Jeu correspond à {$scannedStep->cleanName()}, que tu as déjà réussi ! Trouve d'abord la prochaine destination sur la carte papier avec la loupe.";
                } elseif ($scannedNumber !== null && $scannedNumber > $currentStep->id) {
                    $this->scanTitle = 'Holà, téléportation détectée !';
                    $this->scanSubtitle = "Holà, téléportation détectée ! Ce Maître du Jeu ne peut pas encore être devant toi… Trouve d'abord la destination sur la carte papier avec la loupe !";
                } else {
                    $this->scanTitle = 'Pas si vite, explorateur !';
                    $this->scanSubtitle = "Holà, téléportation détectée ! Ce Maître du Jeu ne peut pas encore être devant toi… Trouve d'abord la destination sur la carte papier avec la loupe !";
                }
            } else {
                $this->scanTitle = 'Mince ! Mauvais QR Code...';
                $this->scanSubtitle = "Ce QR code inconnu ne fait pas partie du carnet de Gali ! Scanne un repère imprimé sur la carte papier pour l'étape {$currentStep->id}.";
            }

            $this->error = "Code invalide pour l'étape actuelle. Vous devez scanner le QR code de la carte (MAP_LOC_{$currentStep->id}).";
            $this->scanStatus = 'error';

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

                $this->scanStatus = 'success';
                $this->dispatch('step-updated');

                if ($user->current_step > 6 || $user->fragments()->count() >= 6) {
                    $this->message = "QR MJ validé ! Fragment obtenu pour l'étape {$currentStep->id}. Vous avez réuni les 6 fragments ! Rendez-vous dans l'Inventaire pour voir la victoire !";
                    $this->scanTitle = 'Victoire Absolue !';
                    $this->scanSubtitle = 'Les 6 fragments sont réunis ! Rendez-vous dans votre inventaire pour ouvrir le coffre de la Lampe Cœur et récupérer votre lot Asmodee.';
                    $this->scanTargetTab = 'inventory';
                } else {
                    $this->message = "QR MJ validé ! Fragment obtenu pour l'étape {$currentStep->id}.";
                    $this->scanTitle = 'Bravo ! Épreuve réussie !';
                    $this->scanSubtitle = "Fragment n°{$currentStep->id} débloqué ! Préparez-vous pour l'étape suivante.";
                    $this->scanTargetTab = 'hub';
                }

                return;
            }

            $scannedStep = $resolved['step'];
            $scannedNumber = $resolved['step_number'] ?? $scannedStep?->id;
            $currentCleanName = $currentStep->cleanName();

            if ($resolved['type'] === 'mj') {
                if ($scannedStep && $scannedNumber !== null && $scannedNumber < $currentStep->id) {
                    $this->scanTitle = 'Mince ! Mauvais Maître du Jeu...';
                    $this->scanSubtitle = "Ce badge appartient à {$scannedStep->cleanName()}, une étape déjà validée ! Rejoins plutôt {$currentCleanName} pour rencontrer le bon animateur.";
                } elseif ($scannedNumber !== null && $scannedNumber > $currentStep->id) {
                    $this->scanTitle = 'Holà, téléportation détectée !';
                    $this->scanSubtitle = "Ce badge appartient à une étape que tu n'as pas encore débloquée ! Rejoins d'abord {$currentCleanName} pour valider l'étape {$currentStep->id}.";
                } else {
                    $this->scanTitle = 'Mince ! Mauvais Maître du Jeu...';
                    $this->scanSubtitle = "Ce badge ne correspond pas au Maître du Jeu de {$currentCleanName} ! Scanne le badge officiel sur place pour valider l'étape {$currentStep->id}.";
                }
            } elseif ($resolved['type'] === 'map') {
                if ($scannedNumber === $currentStep->id) {
                    $this->scanTitle = 'Emplacement déjà validé !';
                    $this->scanSubtitle = "Tu as déjà localisé {$currentCleanName} sur la carte ! Rends-toi sur place et scanne le badge du Maître du Jeu.";
                } elseif ($scannedStep && $scannedNumber !== null && $scannedNumber < $currentStep->id) {
                    $this->scanTitle = 'Emplacement déjà validé !';
                    $this->scanSubtitle = "Tu es déjà passé par {$scannedStep->cleanName()} ! Suis la boussole vers {$currentCleanName} pour scanner le Maître du Jeu sur place.";
                } else {
                    $this->scanTitle = 'Emplacement déjà validé !';
                    $this->scanSubtitle = "L'emplacement actuel ({$currentCleanName}) est déjà validé, et ce repère appartient à une autre étape ! Scanne d'abord le Maître du Jeu sur place.";
                }
            } else {
                $this->scanTitle = 'Mince ! Mauvais QR Code...';
                $this->scanSubtitle = "Ce QR code inconnu ne fait pas partie du carnet de Gali ! Rejoins {$currentCleanName} et scanne le badge officiel du Maître du Jeu.";
            }

            $this->error = "Code invalide pour l'étape actuelle. Vous devez scanner le QR code du Maître du Jeu (MJ_STEP_{$currentStep->id}).";
            $this->scanStatus = 'error';

            return;
        }

        $this->error = 'Phase de jeu inconnue.';
        $this->scanStatus = 'error';
        $this->scanTitle = 'Phase Inconnue';
        $this->scanSubtitle = 'Impossible de déterminer la phase de jeu.';
    }

    public function render()
    {
        return view('livewire.game.scanner', [
            'user' => Auth::user(),
        ]);
    }
}
