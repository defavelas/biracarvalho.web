@props(['type' => 'button', 'position' => 'top-left', 'onclick' => '', 'ariaLabel' => ''])

<button 
    type="{{ $type }}"
    class="floating-btn floating-btn-{{ str_replace('top-', '', $position) }}"
    onclick="{{ $onclick }}"
    aria-label="{{ $ariaLabel }}"
    {{ $attributes }}
>
    {{ $slot }}
</button> 