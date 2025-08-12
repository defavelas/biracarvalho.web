<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Locations;

use App\Enums\LocationType;
use App\Livewire\Forms\LocationForm;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

final class Create extends Component
{
    use WithFileUploads;

    public LocationForm $form;
    public bool $showModal = false;
    public array $photos = [];

    #[On('open-create-modal')]
    public function openModal(): void
    {
        $this->form->reset();
        $this->photos = [];
        $this->showModal = true;
    }

    public function save(): void
    {
        $location = $this->form->store();

        // Handle photo uploads
        foreach ($this->photos as $photo) {
            $path = $photo->store('locations', 'public');
            $location->images()->create([
                'image_path' => $path,
                'published_at' => $this->form->published ? now() : null,
            ]);
        }

        $this->closeModal();
        $this->dispatch('location-created');
        session()->flash('message', 'Local criado com sucesso.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->form->reset();
        $this->photos = [];
    }

    public function removePhoto(int $index): void
    {
        unset($this->photos[$index]);
        $this->photos = array_values($this->photos);
    }

    public function render()
    {
        return view('livewire.admin.locations.create', [
            'locationTypes' => LocationType::options(),
        ]);
    }
}
