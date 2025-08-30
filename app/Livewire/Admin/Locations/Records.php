<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Locations;

use App\Enum\Location\Category;
use App\Models\Location;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class Records extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'category')]
    public string $categoryFilter = '';

    #[Url(as: 'status')]
    public string $statusFilter = '';

    public bool $showDeleteModal = false;
    public ?Location $selectedLocation = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
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
            
            // Clear location caches when a location is deleted
            app(\App\Services\LocationService::class)->clearCache();
            
            $this->showDeleteModal = false;
            $this->selectedLocation = null;

            session()->flash('message', 'Local excluído com sucesso.');
        }
    }

    public function closeModals(): void
    {
        $this->showDeleteModal = false;
        $this->selectedLocation = null;
    }

    public function approve(Location $location): void
    {
        $location->approve();
        session()->flash('message', __('Local aprovado com sucesso.'));
    }

    public function getLocationsProperty()
    {
        return Location::query()
            ->when($this->search, fn($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->when($this->categoryFilter, fn($query) => $query->where('category', $this->categoryFilter))
            ->when($this->statusFilter, function ($query) {
                return $this->statusFilter === 'pending' 
                    ? $query->pending() 
                    : $query->published();
            })
            ->with(['images', 'infos'])
            ->latest()
            ->paginate(25);
    }

    public function render()
    {
        return view('livewire.admin.locations.records', [
            'locations' => $this->locations,
            'categories' => Category::options(),
        ])
            ->title('Bira Carvalho: Locais de acessibilidade')
            ->layout('layouts.admin');
    }
}
