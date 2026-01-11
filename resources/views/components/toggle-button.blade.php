@props([
    'id' => null,
    'name' => null,
    'value' => false,
    'label' => '',
    'trackClass' => 'bg-black/20 border-white',
    'thumbClass' => 'bg-white',
    'labelClass' => 'text-white',
    'disabled' => false,
])

@php
    $id = $id ?? ($name ?? 'toggle-' . uniqid());
    $isChecked = (bool) $value;
@endphp

<label class="flex items-center space-x-3 md:space-x-2 cursor-pointer py-1 {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
    for="{{ $id }}">
    <div class="relative">
        <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1"
            {{ $isChecked ? 'checked' : '' }} {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->whereStartsWith('wire:') }} class="sr-only" role="switch"
            aria-checked="{{ $isChecked ? 'true' : 'false' }}"
            aria-describedby="{{ $id }}-description">
        <div
            class="w-8 h-5 md:w-[29px] md:h-4 rounded-full border-1 transition-colors duration-200 ease-in-out {{ $trackClass }} {{ $isChecked ? 'bg-opacity-100 ring-2 ring-secondary/50 ring-offset-1' : 'bg-opacity-50' }} relative flex items-center focus-within:ring-2 focus-within:ring-secondary focus-within:ring-offset-2">
            <div
                class="absolute left-0.5 w-3.5 h-3.5 md:w-2.5 md:h-2.5 border-1 border-transparent rounded-full transition-transform duration-200 ease-in-out {{ $thumbClass }} {{ $isChecked ? 'transform translate-x-3 md:translate-x-3' : 'transform translate-x-0' }} shadow-sm">
            </div>
        </div>
    </div>
    <span class="{{ $labelClass }}">{{ $label }}</span>
    <span id="{{ $id }}-description" class="sr-only">
        Filtro {{ $isChecked ? 'ativado' : 'desativado' }}. Pressione espaço ou enter para alternar.
    </span>
</label>
