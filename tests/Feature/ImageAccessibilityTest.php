<?php

declare(strict_types=1);

use App\Enum\Location\Type;
use App\Models\Location;
use App\Services\LocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('builds contextual alt text for location images in map payloads', function (): void {
    $location = Location::query()->create([
        'name' => 'Praça da Amizade',
        'description' => 'praça pública',
        'type' => Type::NON_ACCESSIBLE,
        'latitude' => -22.8666,
        'longitude' => -43.2338,
        'authors' => 'Equipe Bira',
        'published_at' => now(),
    ]);

    $location->infos()->create([
        'title' => 'Entrada principal',
        'value' => 'degrau alto e ausência de rampa',
    ]);

    $location->images()->create([
        'image_path' => 'images/locations/praca-da-amizade.jpg',
        'published_at' => now(),
    ]);

    $payload = app(LocationService::class)->transformForMap($location->fresh(['images', 'infos']));

    expect($payload['images'][0]['alt'])
        ->toContain('Praça da Amizade')
        ->toContain('Entrada principal')
        ->toContain('degrau alto e ausência de rampa');
});
