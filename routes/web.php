<?php

declare(strict_types=1);

use App\Livewire;
use Illuminate\Support\Facades\Route;

Route::get('/', Livewire\App::class)->name('map');

Route::get('/admin', fn() => redirect()->route('login'));

Route::prefix('admin')->group(function (): void {
    Route::get('/login', Livewire\Admin\Login::class)->name('login');

    Route::middleware('auth')->group(function (): void {
        Route::get('/locations', Livewire\Admin\Locations\Records::class)
            ->name('admin.locations.records');

        Route::get('/locations/create', Livewire\Admin\Locations\Create::class)
            ->name('admin.locations.create');

        Route::get('/locations/{location}/edit', Livewire\Admin\Locations\Edit::class)
            ->name('admin.locations.edit');

        Route::get('/csv-import', Livewire\Admin\CsvImport::class)
            ->name('admin.csv-import');

        Route::post('/logout', function () {
            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect()->route('login');
        })->name('admin.logout');
    });
});
