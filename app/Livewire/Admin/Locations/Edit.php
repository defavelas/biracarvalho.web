<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Locations;

use App\Enums\LocationType;
use App\Livewire\Forms\LocationForm;
use App\Models\Location;
use Livewire\Component;
use Livewire\WithFileUploads;

final class Edit extends Component
{
    use WithFileUploads;

    public LocationForm $form;
    public Location $location;
    public array $photos = [];

    public function mount(Location $location): void
    {
        $this->location = $location;
        $this->form->setLocation($location);
    }

    public function save(): void
    {
        $this->form->update();

        // Handle new photo uploads
        foreach ($this->photos as $photo) {
            $path = $photo->store('locations', 'public');
            $this->location->images()->create([
                'image_path' => $path,
                'published_at' => $this->form->published ? now() : null,
            ]);
        }

        $this->photos = [];
        $this->dispatch('location-updated');
        session()->flash('message', 'Local atualizado com sucesso.');
    }

    public function removePhoto(int $index): void
    {
        unset($this->photos[$index]);
        $this->photos = array_values($this->photos);
    }

    public function removeExistingImage(string $imageId): void
    {
        $image = $this->location->images()->find($imageId);
        if ($image) {
            $image->delete();
            $this->location->refresh();
        }
    }

    public function render()
    {
        return view('livewire.admin.locations.edit', [
            'locationTypes' => LocationType::options(),
        ]);
    }
}
