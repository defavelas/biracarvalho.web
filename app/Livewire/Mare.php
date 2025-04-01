<?php

namespace App\Livewire;

use App\Services;
use Livewire\{Attributes, Component};

class Mare extends Component
{

    #[Attributes\Computed]
    public function data()
    {
        return Services\MapDataService::getMapData();
    }

    public function render()
    {
        return view('livewire.mare', ['data' => $this->data])->layout('layouts.app');
    }
}
