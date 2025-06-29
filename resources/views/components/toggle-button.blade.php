@props([
    'id' => null,
    'name' => null,
    'value' => false,
    'label' => '',
    'trackClass' => 'bg-primary border-secondary',
    'thumbClass' => 'bg-secondary',
    'labelClass' => 'text-secondary',
    'disabled' => false,
])

@php
    $id = $id ?? ($name ?? 'toggle-' . uniqid());
    $isChecked = (bool) $value;
@endphp

<label class="flex items-center space-x-2 cursor-pointer {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
    for="{{ $id }}">
    <div class="relative">
        <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1"
            {{ $isChecked ? 'checked' : '' }} {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->whereStartsWith('wire:') }} class="sr-only" role="switch"
            aria-checked="{{ $isChecked ? 'true' : 'false' }}">
        <div
            class="w-[29px] h-4 rounded-full border-2 transition-colors duration-200 ease-in-out {{ $trackClass }} {{ $isChecked ? 'bg-opacity-100' : 'bg-opacity-50' }} relative flex items-center">
            <div
                class="absolute left-0.5 w-2.5 border-1 border-transparent h-2.5 rounded-full transition-transform duration-200 ease-in-out {{ $thumbClass }} {{ $isChecked ? 'transform translate-x-3' : 'transform translate-x-0' }} shadow-sm">
            </div>
        </div>
    </div>
    <span class="text-sm font-medium {{ $labelClass }}">{{ $label }}</span>
</label>
