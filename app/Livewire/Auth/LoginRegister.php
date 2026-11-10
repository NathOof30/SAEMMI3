<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;

class LoginRegister extends Component
{
    public string $mode = 'landing'; // 'landing', 'register', 'login'

    public string $team_name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function setMode(string $mode): void
    {
        if (in_array($mode, ['landing', 'register', 'login'], true)) {
            $this->mode = $mode;
            $this->resetErrorBag();
        }
    }

    public function register(): void
    {
        $this->team_name = trim($this->team_name);
        $this->email = trim($this->email);

        $this->validate([
            'team_name' => 'required|string|min:2|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $this->team_name,
            'team_name' => $this->team_name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'avatar' => 'gali',
            'reward_picked' => false,
            'reward_token' => Str::random(32),
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ]);

        Auth::login($user, remember: true);

        $this->dispatch('user-logged-in');

        $this->redirect('/', navigate: true);
    }

    public function login(): void
    {
        $this->email = trim($this->email);

        $this->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], remember: true)) {
            session()->regenerate();
            $this->dispatch('user-logged-in');
            $this->redirect('/', navigate: true);

            return;
        }

        $this->addError('email', 'Identifiants invalides (email ou mot de passe incorrect).');
    }

    public function render()
    {
        return view('livewire.auth.login-register');
    }
}
