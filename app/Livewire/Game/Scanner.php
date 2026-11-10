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

        if ($user->phase === 'GUESSING_LOCATION') {
            $isMapMatch = ($upperCode === $expectedMapCode)
                || ($upperCode === "MAP_LOC_{$currentStep->id}")
                || ($upperCode === "MAP-STEP-{$currentStep->id}");

            if ($isMapMatch) {
                $user->phase = 'WALKING_TO_TARGET';
                $user->save();

                $this->message = "QR Map validé ! Direction la cible pour l'étape {$user->current_step} : {$currentStep->name}.";
                $this->scanStatus = 'success';
                $this->scanTitle = 'Bravo ! Emplacement trouvé !';
                $this->scanSubtitle = "Cap activé vers {$currentStep->name} ! Suivez l'aiguille de la boussole.";
                $this->scanTargetTab = 'hub';
                $this->dispatch('step-updated');

                return;
            }

            if (preg_match('/^MAP[-_]/i', $upperCode)) {
                $this->scanTitle = 'Mince ! Je ne crois pas que ça soit...';
                $this->scanSubtitle = "Ce repère ne correspond pas à votre étape actuelle ({$currentStep->name}). Cherchez un autre symbole sur la carte !";
            } elseif (preg_match('/^MJ[-_]/i', $upperCode)) {
                $this->scanTitle = 'Pas si vite, explorateur !';
                $this->scanSubtitle = "Vous devez d'abord localiser le lieu sur la carte avant de rencontrer le Maître du Jeu.";
            } else {
                $this->scanTitle = 'Mince ! Mauvais QR Code...';
                $this->scanSubtitle = "Vous devez scanner le repère de carte de l'étape {$currentStep->id}.";
            }

            $this->error = "Code invalide pour l'étape actuelle. Vous devez scanner le QR code de la carte (MAP_LOC_{$currentStep->id}).";
            $this->scanStatus = 'error';

            return;
        }

        if ($user->phase === 'WALKING_TO_TARGET') {
            $isMjMatch = ($upperCode === $expectedMjCode)
                || ($upperCode === "MJ_STEP_{$currentStep->id}")
                || ($upperCode === "MJ-STEP-{$currentStep->id}");

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
                    $this->scanSubtitle = 'Les 6 fragments sont réunis ! Rendez-vous dans votre inventaire pour ouvrir le coffre Asmodee.';
                    $this->scanTargetTab = 'inventory';
                } else {
                    $this->message = "QR MJ validé ! Fragment obtenu pour l'étape {$currentStep->id}.";
                    $this->scanTitle = 'Bravo ! Épreuve réussie !';
                    $this->scanSubtitle = "Fragment n°{$currentStep->id} débloqué ! Préparez-vous pour l'étape suivante.";
                    $this->scanTargetTab = 'hub';
                }

                return;
            }

            if (preg_match('/^MJ[-_]/i', $upperCode)) {
                $this->scanTitle = 'Mince ! Mauvais Maître du Jeu...';
                $this->scanSubtitle = "Ce badge correspond à une autre étape. Cherchez le Maître du Jeu de l'étape {$currentStep->id} !";
            } elseif (preg_match('/^MAP[-_]/i', $upperCode)) {
                $this->scanTitle = 'Emplacement déjà validé !';
                $this->scanSubtitle = "Vous avez déjà trouvé le lieu ! Vous devez maintenant accomplir l'épreuve du Maître du Jeu.";
            } else {
                $this->scanTitle = 'Mince ! Mauvais QR Code...';
                $this->scanSubtitle = "Vous devez scanner le badge du Maître du Jeu pour valider l'étape {$currentStep->id}.";
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
