@props(['type' => 'button', 'position' => 'top-left', 'onclick' => '', 'ariaLabel' => ''])

@php
    $positionClasses = [
        'top-left' => 'top-5 left-5',
        'top-right' => 'top-5 right-5',
        'bottom-left' => 'bottom-5 left-5',
        'bottom-right' => 'bottom-5 right-5',
    ];
    $positionClass = $positionClasses[$position] ?? 'top-5 left-5';
@endphp

<button 
    type="{{ $type }}"
    class="absolute z-[1001] w-11 h-11 rounded-full bg-white shadow-lg border-0 cursor-pointer flex items-center justify-center transition-all duration-200 ease-in-out hover:shadow-xl hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-lg {{ $positionClass }}"
    onclick="{{ $onclick }}"
    aria-label="{{ $ariaLabel }}"
    {{ $attributes }}
>
    {{ $slot }}
</button> 