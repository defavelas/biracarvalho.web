<?php

declare(strict_types=1);

use App\Livewire;
use Illuminate\Support\Facades\Route;

Route::get('/', Livewire\App::class)->name('map');

Route::get('/api/locations/map', function () {
    $locationService = app(\App\Services\LocationService::class);
    $locations = $locationService->getPublishedLocationsForMap();
    return response()->json($locationService->transformCollectionForMap($locations));
});

Route::get('/admin', fn() => redirect()->route('admin.dashboard'));

Route::prefix('admin')->group(function (): void {
    Route::get('/login', Livewire\Admin\Login::class)->name('login');

    Route::middleware('auth')->group(function (): void {
        Route::get('/dashboard', Livewire\Admin\Dashboard::class)
            ->name('admin.dashboard');
            
        Route::get('/locations', Livewire\Admin\Locations\Records::class)
            ->name('admin.locations.records');

        Route::post('/sync-kobo', function () {
            try {
                // Dispatch import jobs for both accessible and non-accessible locations
                \App\Jobs\Kobo\ImportAccessibleLocationsJob::dispatch();
                \App\Jobs\Kobo\ImportNonAccessibleLocationsJob::dispatch();
                
                return response()->json([
                    'success' => true, 
                    'message' => 'Sincronização iniciada com sucesso! Os registros serão processados e ficarão pendentes de aprovação.'
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Sync error', ['error' => $e->getMessage()]);
                return response()->json(['success' => false, 'message' => 'Erro na sincronização: ' . $e->getMessage()]);
            }
        })->name('admin.sync-kobo');

        Route::post('/logout', function () {
            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect()->route('login');
        })->name('admin.logout');
    });
});
