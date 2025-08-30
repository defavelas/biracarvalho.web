<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enum\Location\Category;
use App\Services\LocationService;
use Livewire\Attributes\On;
use Livewire\Component;

final class SearchSidebar extends Component
{
    public string $search = '';

    public array $categoryFilters = [
        'accessible' => false,
        'non_accessible' => false,
    ];

    public bool $collapsed = false;
    public bool $resultsOpen = true;

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
        if ( ! empty($this->search)) {
            $this->resultsOpen = true;
        }
    }

    /**
     * Handle category filter updates.
     */
    public function updatedCategoryFilters(): void
    {
        $this->loadResults();
        $this->dispatch('results-updated', results: $this->results);
        if (array_sum($this->categoryFilters) > 0) {
            $this->resultsOpen = true;
        }
    }

    /**
     * Toggle the sidebar collapsed state.
     */
    public function toggleSidebar(): void
    {
        $this->collapsed = ! $this->collapsed;
    }

    /**
     * Clear all filters and reset search.
     */
    public function clearFilters(): void
    {
        $this->categoryFilters = [
            'accessible' => false,
            'non_accessible' => false,
        ];

        $this->search = '';
        $this->selectedLocationId = null;
        $this->resultsOpen = false;

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
     * Close the results panel for better map navigation.
     */
    public function closeResults(): void
    {
        $this->resultsOpen = false;
    }

    /**
     * Open the results panel.
     */
    public function openResults(): void
    {
        $this->resultsOpen = true;
    }

    /**
     * Focus on a specific location and update the map.
     */
    public function focusLocation($locationId): void
    {
        $this->selectedLocationId = (string) $locationId;

        // Close results on mobile when focusing on a location
        $this->resultsOpen = false;

        $this->dispatch('focus-location', locationId: (string) $locationId);

        $this->js("
            if (window.mapComponentInstance) {
                window.mapComponentInstance.closeMapCard();
                window.mapComponentInstance.focusLocation('{$locationId}');
            }
        ");
    }

    /**
     * Render the component view.
     */
    public function render()
    {
        return view('livewire.search-sidebar');
    }

    /**
     * Load and filter results based on current search and filter criteria.
     */
    private function loadResults(): void
    {
        try {
            $locationService = app(LocationService::class);
            
            $activeCategories = array_keys(array_filter($this->categoryFilters ?? []));
            
            // Ensure we always pass an array
            if (!is_array($activeCategories)) {
                $activeCategories = [];
            }
            
            $locations = $locationService->searchLocations($this->search ?? '', $activeCategories);
            
            $this->results = $locationService->transformCollectionForMap($locations);
            $this->totalResults = count($this->results);

            $this->dispatch('results-updated', results: $this->results);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading results in SearchSidebar', [
                'error' => $e->getMessage(),
                'search' => $this->search ?? null,
                'categoryFilters' => $this->categoryFilters ?? null,
            ]);
            
            // Fallback to empty results
            $this->results = [];
            $this->totalResults = 0;
            $this->dispatch('results-updated', results: $this->results);
        }
    }
}
