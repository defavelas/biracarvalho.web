@props([
    'label' => null,
    'error' => null,
    'required' => false,
    'placeholder' => 'Selecione uma opção',
    'icon' => null,
    'help' => null,
    'options' => [],
    'valueField' => 'value',
    'labelField' => 'label',
])

<div class="space-y-2">
    @if($label)
        <label {{ $attributes->only(['for', 'id']) }} class="block text-sm font-medium text-secondary mb-2">
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
    
    <div class="relative" x-data="{ 
        open: false, 
        selected: $wire.entangle('{{ $attributes->wire('model')->value() }}'),
        selectedLabel: ''
    }" x-init="
        if (selected && {{ json_encode($options) }}.length > 0) {
            const option = {{ json_encode($options) }}.find(opt => opt.{{ $valueField }} == selected);
            selectedLabel = option ? option.{{ $labelField }} : '';
        }
    ">
        <!-- Select Button -->
        <button 
            type="button"
            @click="open = !open"
            @click.away="open = false"
            {{ $attributes->except(['wire:model', 'for', 'id'])->class([
                'w-full min-w-48 bg-white px-4 py-2 border rounded-md focus:outline-none focus:ring-4 placeholder:text-accent-dark transition-colors duration-200 text-left',
                'border-red-500 focus:ring-red-500/25 focus:border-red-500' => $error,
                'border-gray-300 focus:ring-primary/25 focus:border-primary' => !$error,
            ]) }}
            :aria-expanded="open"
            aria-haspopup="listbox"
            role="combobox"
        >
            <span class="flex items-center">
                <span x-text="selectedLabel || '{{ $placeholder }}'" :class="selectedLabel ? 'text-gray-900' : 'text-gray-500'"></span>
            </span>
            <span class="ml-3 absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <svg 
                    :class="{ 'rotate-180': open }"
                    class="h-5 w-5 text-gray-400 transition-transform duration-200" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </span>
        </button>

        <!-- Options Dropdown -->
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute z-50 mt-1 w-full bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto"
            role="listbox"
            style="display: none;"
        >
            @if(empty($options))
                <div class="px-4 py-3 text-sm text-gray-500">
                    Nenhuma opção disponível
                </div>
            @else
                @foreach($options as $option)
                    <div 
                        @click="
                            selected = '{{ is_array($option) ? $option[$valueField] : $option->$valueField }}'; 
                            selectedLabel = '{{ is_array($option) ? $option[$labelField] : $option->$labelField }}'; 
                            open = false;
                        "
                        :class="selected === '{{ is_array($option) ? $option[$valueField] : $option->$valueField }}' ? 'bg-primary/10 text-primary' : 'text-gray-900 hover:bg-gray-50'"
                        class="cursor-pointer select-none relative px-4 py-3 transition-colors duration-150"
                        role="option"
                        :aria-selected="selected === '{{ is_array($option) ? $option[$valueField] : $option->$valueField }}'"
                    >
                        <div class="flex items-center">
                            <span class="block truncate">
                                {{ is_array($option) ? $option[$labelField] : $option->$labelField }}
                            </span>
                            <span 
                                x-show="selected === '{{ is_array($option) ? $option[$valueField] : $option->$valueField }}'"
                                class="absolute inset-y-0 right-0 flex items-center pr-4"
                            >
                                <svg class="h-4 w-4 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
    
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
