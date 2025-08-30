<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\LocationType;
use App\Models\Location;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

final class LocationForm extends Form
{
    public ?Location $location = null;

    #[Validate]
    public string $type = '';

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string')]
    public string $address = '';

    #[Validate('nullable|string')]
    public string $description = '';

    #[Validate('required|numeric|between:-90,90')]
    public string $latitude = '';

    #[Validate('required|numeric|between:-180,180')]
    public string $longitude = '';

    #[Validate('boolean')]
    public bool $published = false;

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(LocationType::class)],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'published' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'O campo tipo é obrigatório.',
            'type.enum' => 'O tipo selecionado é inválido.',
            'name.required' => 'O campo nome é obrigatório.',
            'name.string' => 'O campo nome deve ser um texto.',
            'name.max' => 'O campo nome não pode ter mais de 255 caracteres.',
            'address.required' => 'O campo endereço é obrigatório.',
            'address.string' => 'O campo endereço deve ser um texto.',
            'description.string' => 'O campo descrição deve ser um texto.',
            'latitude.required' => 'O campo latitude é obrigatório.',
            'latitude.numeric' => 'O campo latitude deve ser um número.',
            'latitude.between' => 'O campo latitude deve estar entre -90 e 90.',
            'longitude.required' => 'O campo longitude é obrigatório.',
            'longitude.numeric' => 'O campo longitude deve ser um número.',
            'longitude.between' => 'O campo longitude deve estar entre -180 e 180.',
            'published.boolean' => 'O campo publicado deve ser verdadeiro ou falso.',
        ];
    }

    public function setLocation(Location $location): void
    {
        $this->location = $location;
        $this->type = $location->type->value;
        $this->name = $location->name;
        $this->address = $location->address;
        $this->description = $location->description ?? '';
        $this->latitude = (string) $location->latitude;
        $this->longitude = (string) $location->longitude;
        $this->published = $location->isApproved();
    }

    public function store(): Location
    {
        $this->validate();

        return Location::create([
            'type' => $this->type,
            'name' => $this->name,
            'address' => $this->address,
            'description' => $this->description,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'published_at' => $this->published ? now() : null,
        ]);
    }

    public function update(): void
    {
        $this->validate();

        $this->location->update([
            'type' => $this->type,
            'name' => $this->name,
            'address' => $this->address,
            'description' => $this->description,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'published_at' => $this->published ? now() : null,
        ]);
    }

    public function reset(...$properties): void
    {
        $this->location = null;
        $this->type = '';
        $this->name = '';
        $this->address = '';
        $this->description = '';
        $this->latitude = '';
        $this->longitude = '';
        $this->published = false;

        parent::reset(...$properties);
    }
}
