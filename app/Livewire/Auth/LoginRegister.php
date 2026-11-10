<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class LoginRegister extends Component
{
    #[Validate('required|string|min:2|max:100')]
    public string $team_name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    public function login(): void
    {
        $this->team_name = trim($this->team_name);
        $this->email = trim($this->email);
        $this->validate();

        $user = User::where('email', $this->email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $this->team_name,
                'team_name' => $this->team_name,
                'email' => $this->email,
                'current_step' => 1,
                'phase' => 'GUESSING_LOCATION',
            ]);
        } elseif (! empty($this->team_name)) {
            $user->update([
                'team_name' => $this->team_name,
                'name' => $this->team_name,
            ]);
        }

        Auth::login($user, remember: true);

        $this->dispatch('user-logged-in');

        $this->redirect('/', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login-register');
    }
}
