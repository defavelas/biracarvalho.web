<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Reactive;

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

    public function mount(): void
    {
        $this->loadResults();
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
        $this->loadResults();
    }

    private function loadResults(): void
    {
        // Mock data for now - in real implementation, this would query the database
        $mockResults = [
            [
                'id' => 1,
                'name' => 'Centro de Saúde da Maré',
                'address' => 'Rua Principal, 123',
                'accessibility_level' => 'acessivel',
                'latitude' => -22.8666,
                'longitude' => -43.2338,
            ],
            [
                'id' => 2,
                'name' => 'Escola Municipal',
                'address' => 'Av. Brasil, 456',
                'accessibility_level' => 'parcial_acessivel',
                'latitude' => -22.8700,
                'longitude' => -43.2300,
            ],
            [
                'id' => 3,
                'name' => 'Mercado Local',
                'address' => 'Rua das Flores, 789',
                'accessibility_level' => 'nao_acessivel',
                'latitude' => -22.8630,
                'longitude' => -43.2370,
            ],
        ];

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