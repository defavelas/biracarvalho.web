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

    /**
     * Initialize the component with optional collapsed state.
     */
    public function mount($collapsed = false): void
    {
        $this->collapsed = $collapsed;
        $this->loadResults();
    }

    /**
     * Highlight a specific location in the sidebar.
     */
    #[On('highlight-sidebar-location')]
    public function highlightLocation($locationId): void
    {
        $this->selectedLocationId = $locationId;
    }

    /**
     * Handle search input updates.
     */
    public function updatedSearch(): void
    {
        $this->loadResults();
    }

    /**
     * Handle accessibility filter updates.
     */
    public function updatedAccessibilityFilters(): void
    {
        $this->loadResults();
        $this->dispatch('results-updated', results: $this->results);
    }

    /**
     * Toggle the sidebar collapsed state.
     */
    public function toggleSidebar(): void
    {
        $this->collapsed = !$this->collapsed;
    }

    /**
     * Clear all filters and reset search.
     */
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
        
        $this->dispatch('filters-cleared');
        $this->dispatch('results-updated', results: $this->results);
        
        $this->js("
            if (window.mapComponentInstance) {
                window.mapComponentInstance.closeMapCard();
                window.mapComponentInstance.updateMarkers();
            }
        ");
    }

    /**
     * Focus on a specific location and update the map.
     */
    public function focusLocation($locationId): void
    {
        $this->selectedLocationId = (string) $locationId;
        
        $this->dispatch('focus-location', locationId: (string) $locationId);
        
        $this->js("
            if (window.mapComponentInstance) {
                window.mapComponentInstance.closeMapCard();
                window.mapComponentInstance.focusLocation('$locationId');
            }
        ");
    }

    /**
     * Load and filter results based on current search and filter criteria.
     */
    private function loadResults(): void
    {
        $mockResults = config('places.mock_locations', []);

        if (!empty($this->search)) {
            $mockResults = array_filter($mockResults, function ($result) {
                return stripos($result['name'], $this->search) !== false ||
                       stripos($result['address'], $this->search) !== false;
            });
        }

        $activeFilters = array_keys(array_filter($this->accessibilityFilters));
        if (!empty($activeFilters)) {
            $mockResults = array_filter($mockResults, function ($result) use ($activeFilters) {
                return in_array($result['accessibility_level'], $activeFilters);
            });
        }

        $this->results = array_values($mockResults);
        $this->totalResults = count($this->results);
        
        $this->dispatch('results-updated', results: $this->results);
    }

    /**
     * Render the component view.
     */
    public function render()
    {
        return view('livewire.search-sidebar');
    }
} 