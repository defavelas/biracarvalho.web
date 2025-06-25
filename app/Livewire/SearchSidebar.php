<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Reactive;
use Livewire\Attributes\On;

class SearchSidebar extends Component
{
    public string $search = '';
    
    public array $accessibilityFilters = [
        'acessivel' => false,
        'nao_acessivel' => false,
        'parcial_acessivel' => false,
    ];
    
    public bool $collapsed = false;
    
    public array $results = [];
    public int $totalResults = 0;
    public ?string $selectedLocationId = null;

    public function mount($collapsed = false): void
    {
        $this->collapsed = $collapsed;
        $this->loadResults();
    }

    #[On('highlight-sidebar-location')]
    public function highlightLocation($locationId): void
    {
        $this->selectedLocationId = $locationId;
    }

    public function updatedSearch(): void
    {
        $this->loadResults();
    }

    public function updatedAccessibilityFilters(): void
    {
        $this->loadResults();
    }

    public function toggleSidebar(): void
    {
        $this->collapsed = !$this->collapsed;
    }

    public function clearFilters(): void
    {
        $this->accessibilityFilters = [
            'acessivel' => false,
            'nao_acessivel' => false,
            'parcial_acessivel' => false,
        ];
        $this->search = '';
        $this->selectedLocationId = null;
        $this->loadResults();
    }

    public function focusLocation($locationId): void
    {
        // Convert to string to ensure consistency
        $this->selectedLocationId = (string) $locationId;
        
        // Dispatch event to focus on the location on the map
        $this->dispatch('focus-location', locationId: (string) $locationId);
        
        // Also dispatch a browser event for direct JavaScript handling with card closing
        $this->js("
            if (window.mapComponentInstance) {
                // Close all existing cards first
                window.mapComponentInstance.closeMapCard();
                // Then focus on the new location
                window.mapComponentInstance.focusLocation('$locationId');
            }
        ");
    }

    private function loadResults(): void
    {
        // Load mock data from config - in real implementation, this would query the database
        $mockResults = config('places.mock_locations', []);

        // Filter by search term
        if (!empty($this->search)) {
            $mockResults = array_filter($mockResults, function ($result) {
                return stripos($result['name'], $this->search) !== false ||
                       stripos($result['address'], $this->search) !== false;
            });
        }

        // Filter by accessibility levels
        $activeFilters = array_keys(array_filter($this->accessibilityFilters));
        if (!empty($activeFilters)) {
            $mockResults = array_filter($mockResults, function ($result) use ($activeFilters) {
                return in_array($result['accessibility_level'], $activeFilters);
            });
        }

        $this->results = array_values($mockResults);
        $this->totalResults = count($this->results);
        
        // Dispatch event to update map markers
        $this->dispatch('results-updated', results: $this->results);
    }

    public function render()
    {
        return view('livewire.search-sidebar');
    }
} 