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

        Route::post('/logout', function () {
            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect()->route('login');
        })->name('admin.logout');
    });
});
