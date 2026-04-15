<?php

declare(strict_types=1);

use App\Livewire\App as MapApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders a single effective skip link to the map', function (): void {
    Livewire::test(MapApp::class)
        ->assertSee('Pular para o mapa')
        ->assertDontSee('Pular para busca');
});
