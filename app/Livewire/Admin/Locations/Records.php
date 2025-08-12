<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Locations;

use App\Enums\LocationType;
use App\Models\Location;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class Records extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'type')]
    public string $typeFilter = '';

    public bool $showEditModal = false;
    public bool $showDeleteModal = false;
    public ?Location $selectedLocation = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->dispatch('open-create-modal');
    }

    public function edit(Location $location): void
    {
        $this->selectedLocation = $location;
        $this->showEditModal = true;
    }

    public function confirmDelete(Location $location): void
    {
        $this->selectedLocation = $location;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->selectedLocation) {
            $this->selectedLocation->delete();
            $this->showDeleteModal = false;
            $this->selectedLocation = null;

            session()->flash('message', 'Local excluído com sucesso.');
        }
    }

    public function closeModals(): void
    {
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->selectedLocation = null;
    }

    public function getLocationsProperty()
    {
        return Location::query()
            ->when($this->search, fn($query) => $query->search($this->search))
            ->when($this->typeFilter, fn($query) => $query->where('type', $this->typeFilter))
            ->with('images')
            ->latest()
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.admin.locations.records', [
            'locations' => $this->locations,
            'locationTypes' => LocationType::options(),
        ])
            ->title('Bira Carvalho: Locais de acessibilidade')
            ->layout('layouts.admin');
    }
}
