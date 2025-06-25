@props([
    'show' => false,
    'maxWidth' => 'sm',
    'wire' => 'show',
])

@php
    $maxWidthClass = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
    ][$maxWidth];
@endphp

<div x-data="{ show: @entangle($wire) }" x-show="show" x-cloak
    class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto" style="display: none;" role="dialog"
    aria-modal="true" aria-labelledby="modal-title">
    <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm" {{ $attributes->only(['wire:click', '@click']) }}></div>

    <div x-show="show" x-transition:enter="ease-out duration-400 delay-150"
        x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-8"
        class="relative w-full {{ $maxWidthClass }} mx-4 bg-secondary border-4 border-black/15 rounded-lg shadow-xl overflow-hidden"
        x-trap.noscroll="show">
        {{ $slot }}
    </div>
</div>
