<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Location;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

final class Dashboard extends Component
{
    use WithPagination;

    public function approve(Location $location): void
    {
        $location->approve();

        session()->flash('message', __('Local aprovado com sucesso.'));
        $this->resetPage();
    }

    public function reject(Location $location): void
    {
        $location->delete();

        // Clear location caches when a location is deleted
        app(\App\Services\LocationService::class)->clearCache();

        session()->flash('message', __('Local rejeitado e excluído.'));
        $this->resetPage();
    }

    public function getPendingLocationsProperty()
    {
        return Location::query()
            ->pending()
            ->with(['images', 'infos'])
            ->latest()
            ->paginate(25);
    }

    public function getTotalLocationsProperty()
    {
        return Location::count();
    }

    public function getTotalPendingProperty()
    {
        return Location::pending()->count();
    }

    public function getTotalPublishedProperty()
    {
        return Location::published()->count();
    }

    public function getLastUpdatedAtProperty()
    {
        $location = Location::latest('updated_at')->first();

        if (!$location) {
            return null;
        }

        return Carbon::parse($location->updated_at)
            ->setTimezone('America/Sao_Paulo')
            ->format('d/m/Y H:i');
    }

    public function render()
    {
        return view('livewire.admin.dashboard', [
            'pendingLocations' => $this->pendingLocations,
            'totalLocations' => $this->totalLocations,
            'totalPending' => $this->totalPending,
            'totalPublished' => $this->totalPublished,
            'lastUpdatedAt' => $this->lastUpdatedAt,
        ])
            ->title(__('Dashboard'))
            ->layout('layouts.admin');
    }
}
