<?php

declare(strict_types=1);

use App\Livewire;
use Illuminate\Support\Facades\Route;

Route::get('/', Livewire\App::class)->name('map');

Route::get('admin', fn() => redirect()->route('admin.login'));

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', Livewire\Admin\Login::class)->name('login');

    Route::middleware('auth')->group(function (): void {
        Route::get('/locations', Livewire\Admin\Locations\Records::class)->name('locations.records');
        Route::get('/locations/create', Livewire\Admin\Locations\Create::class)->name('locations.create');
        Route::get('/locations/{location}/edit', Livewire\Admin\Locations\Edit::class)->name('locations.edit');
        Route::get('/csv-import', Livewire\Admin\CsvImport::class)->name('csv-import');

        Route::post('/logout', function () {
            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect()->route('admin.login');
        })->name('logout');
    });
});
