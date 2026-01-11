<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;

final class App extends Component
{
    public bool $sidebarCollapsed = false;

    public bool $showAccessibilityModal = false;

    public function toggleSidebar(): void
    {
        $this->sidebarCollapsed = ! $this->sidebarCollapsed;

        $this->dispatch('sidebar-toggled', collapsed: $this->sidebarCollapsed);
    }

    public function openAccessibilityModal(): void
    {
        $this->sidebarCollapsed = true;
        $this->dispatch('sidebar-toggled', collapsed: true);
        $this->showAccessibilityModal = true;
    }

    public function closeAccessibilityModal(): void
    {
        $this->showAccessibilityModal = false;
    }

    public function render()
    {
        return view('livewire.app')
            ->title('Programa Bira Carvalho')
            ->layout('layouts.app');
    }
}
