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

    public function verifyManualCode(): void
    {
        $this->verifyCode($this->manualCode);
    }

    public function verifyCode(?string $code = null): void
    {
        $code = trim((string) ($code !== null && $code !== '' ? $code : $this->manualCode));
        $this->message = null;
        $this->error = null;

        if ($code === '') {
            $this->error = 'Veuillez saisir ou scanner un code.';

            return;
        }

        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            $this->error = 'Utilisateur non connecté.';

            return;
        }

        if ($user->fragments()->count() >= 6 || $user->current_step > 6) {
            $this->message = 'Toutes les étapes ont déjà été validées ! Félicitations !';
            $this->manualCode = '';

            return;
        }

        $currentStep = Step::find($user->current_step);

        if (! $currentStep) {
            $this->error = 'Étape introuvable.';

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
                $this->manualCode = '';
                $this->dispatch('step-updated');

                return;
            }

            $this->error = "Code invalide pour l'étape actuelle. Vous devez scanner le QR code de la carte (MAP_LOC_{$currentStep->id}).";

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

                $this->message = "QR MJ validé ! Fragment obtenu pour l'étape {$currentStep->id}.";
                $this->manualCode = '';
                $this->dispatch('step-updated');

                if ($user->current_step > 6 || $user->fragments()->count() >= 6) {
                    $this->message .= " Vous avez réuni les 6 fragments ! Rendez-vous dans l'Inventaire pour voir la victoire !";
                }

                return;
            }

            $this->error = "Code invalide pour l'étape actuelle. Vous devez scanner le QR code du Maître du Jeu (MJ_STEP_{$currentStep->id}).";

            return;
        }

        $this->error = 'Phase de jeu inconnue.';
    }

    public function render()
    {
        return view('livewire.game.scanner', [
            'user' => Auth::user(),
        ]);
    }
}
