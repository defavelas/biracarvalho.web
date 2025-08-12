<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;

final class App extends Component
{
    public bool $sidebarCollapsed = false;

    public function toggleSidebar(): void
    {
        $this->sidebarCollapsed = ! $this->sidebarCollapsed;

        $this->dispatch('sidebar-toggled', collapsed: $this->sidebarCollapsed);
    }

    public function render()
    {
        return view('livewire.app')
            ->title('Programa Bira Carvalho')
            ->layout('layouts.app');
    }
}
