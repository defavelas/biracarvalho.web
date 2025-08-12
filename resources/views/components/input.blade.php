@props([
    'label' => null,
    'error' => null,
    'required' => false,
    'type' => 'text',
    'icon' => null,
    'help' => null,
])

<div class="space-y-2">
    @if($label)
        <label {{ $attributes->only(['for', 'id']) }} class="block text-sm font-medium text-accent-dark mb-2">
            @if($icon)
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $icon !!}
                </svg>
            @endif
            {{ $label }}
            @if($required)
                <span class="text-red-500 ml-1" aria-label="obrigatório">*</span>
            @endif
        </label>
    @endif
    
    @if($type === 'textarea')
        <textarea
            {{ $attributes->except(['label', 'error', 'required', 'type', 'icon', 'help', 'for', 'id'])->class([
                'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-4 transition-colors duration-200 resize-none',
                'border-red-500 focus:ring-red-500/25 focus:border-red-500' => $error,
                'border-gray-300 focus:ring-primary/25 focus:border-primary' => !$error,
            ]) }}
        ></textarea>
    @else
        <input
            type="{{ $type }}"
            {{ $attributes->except(['label', 'error', 'required', 'type', 'icon', 'help', 'for', 'id'])->class([
                'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-4 transition-colors duration-200',
                'border-red-500 focus:ring-red-500/25 focus:border-red-500' => $error,
                'border-gray-300 focus:ring-primary/25 focus:border-primary' => !$error,
            ]) }}
        />
    @endif
    
    @if($help)
        <p class="text-xs text-accent-dark/60">{{ $help }}</p>
    @endif
    
    @if($error)
        <p class="mt-2 text-sm text-red-600 flex items-center" role="alert">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $error }}
        </p>
    @endif
</div>
