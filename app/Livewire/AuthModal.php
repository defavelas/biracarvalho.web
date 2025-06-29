<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Rule;

class AuthModal extends Component
{
    #[Rule('required|string|min:3')]
    public string $username = '';

    #[Rule('required|string|min:6')]
    public string $password = '';

    public bool $remember = false;

    public bool $showModal = false;

    protected $listeners = ['show-auth-modal' => 'showModal'];

    public function showModal(): void
    {
        $this->showModal = true;
    }

    public function hideModal(): void
    {
        $this->showModal = false;
        $this->reset(['username', 'password', 'remember']);
        $this->resetValidation();
    }

    public function login(): void
    {
        $this->validate();

        // TODO: Implement actual authentication logic
        // For now, just close the modal and show a message
        $this->dispatch('show-notification', message: 'Login implementado em breve!', type: 'info');
        $this->hideModal();
    }

    public function render()
    {
        return view('livewire.auth-modal');
    }
} 