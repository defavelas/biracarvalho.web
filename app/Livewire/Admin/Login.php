<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

final class Login extends Component
{
    public LoginForm $form;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirect(route('admin.locations.records'));
        }
    }

    public function login(): void
    {
        $key = 'login_attempts:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('form.username', 'Muitas tentativas de login. Tente novamente em ' . $seconds . ' segundos.');
            return;
        }

        $this->form->validate();

        $credentials = [
            'email' => $this->form->username,
            'password' => $this->form->password,
        ];

        if (Auth::attempt($credentials)) {
            RateLimiter::clear($key);
            session()->regenerate();
            $this->redirect(route('admin.locations.records'));
        } else {
            RateLimiter::hit($key, 1800); // 30 minutes
            $this->addError('form.username', 'Credenciais inválidas.');
        }
    }

    public function render()
    {
        return view('livewire.admin.login')
            ->title('Programa Bira Carvalho: Login')
            ->layout('layouts.guest');
    }
}
