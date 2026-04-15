<?php

declare(strict_types=1);

use App\Enum\Location\Type;
use App\Livewire\App as MapApp;
use App\Livewire\SearchSidebar;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('opens the desktop sidebar when requested from a client event', function (): void {
    Livewire::test(MapApp::class)
        ->set('sidebarCollapsed', true)
        ->dispatch('open-sidebar')
        ->assertSet('sidebarCollapsed', false)
        ->assertDispatched('sidebar-toggled', collapsed: false);
});

it('reveals a location in the sidebar and keeps the results panel open', function (): void {
    $location = Location::query()->create([
        'name' => 'Centro Cultural',
        'description' => 'Espaço com rampa de acesso.',
        'type' => Type::ACCESSIBLE,
        'latitude' => -22.8666,
        'longitude' => -43.2338,
        'authors' => 'Equipe Bira',
        'published_at' => now(),
    ]);

    Livewire::test(SearchSidebar::class)
        ->set('resultsOpen', false)
        ->dispatch('reveal-location-in-sidebar', locationId: $location->id)
        ->assertSet('selectedLocationId', $location->id)
        ->assertSet('resultsOpen', true)
        ->assertDispatched('sidebar-location-revealed', locationId: $location->id);
});
