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
            {{ $attributes->whereStartsWith('wire:') }} class="peer sr-only" role="switch"
            aria-checked="{{ $isChecked ? 'true' : 'false' }}"
            aria-describedby="{{ $id }}-description">
        <div
            class="w-10 h-6 md:w-[34px] md:h-[18px] rounded-full border transition-all duration-200 ease-in-out {{ $trackClass }} {{ $isChecked ? 'bg-secondary border-secondary shadow-[0_0_0_3px_rgba(206,216,66,0.25)]' : 'bg-black/10 border-white/70' }} relative flex items-center peer-focus-visible:ring-4 peer-focus-visible:ring-secondary peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-primary">
            <div
                class="absolute left-0.5 w-4.5 h-4.5 md:w-3.5 md:h-3.5 border border-transparent rounded-full transition-transform duration-200 ease-in-out {{ $thumbClass }} {{ $isChecked ? 'translate-x-5 md:translate-x-3.5 shadow-md' : 'translate-x-0 shadow-sm' }}">
            </div>
        </div>
    </div>
    <span class="{{ $labelClass }} {{ $isChecked ? 'font-semibold text-secondary' : 'text-white/90' }}">{{ $label }}</span>
    <span id="{{ $id }}-description" class="sr-only">
        Filtro {{ $isChecked ? 'ativado' : 'desativado' }}. Pressione espaço ou enter para alternar.
    </span>
</label>
