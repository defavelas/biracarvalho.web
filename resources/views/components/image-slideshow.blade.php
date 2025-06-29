@props([
    'images' => [],
    'alt' => 'Imagem do local',
    'maxImages' => 5
])

@php
    $displayImages = array_slice($images, 0, $maxImages);
    $totalImages = count($displayImages);
    $slideshowId = 'slideshow-' . uniqid();
@endphp

@if($totalImages > 0)
<div class="relative group" x-data="{ currentSlide: 0, totalSlides: {{ $totalImages }} }">
    <!-- Image Container -->
    <div class="relative h-32 bg-black/25 rounded-lg overflow-hidden">
        @foreach($displayImages as $index => $image)
            <div 
                class="absolute inset-0 transition-opacity duration-300"
                x-show="currentSlide === {{ $index }}"
                x-transition:enter="transition-opacity duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                <img 
                    src="{{ $image }}" 
                    alt="{{ $alt }} - Imagem {{ $index + 1 }} de {{ $totalImages }}"
                    class="w-full h-full object-cover"
                    loading="lazy"
                >
            </div>
        @endforeach

        <!-- Navigation Overlay -->
        @if($totalImages > 1)
            <!-- Previous Button -->
            <button 
                type="button"
                @click="currentSlide = currentSlide === 0 ? totalSlides - 1 : currentSlide - 1"
                class="absolute left-2 top-1/2 transform -translate-y-1/2 w-8 h-8 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-primary/50"
                aria-label="Imagem anterior"
            >
                @svg('heroicon-o-chevron-left', 'w-4 h-4')
            </button>

            <!-- Next Button -->
            <button 
                type="button"
                @click="currentSlide = currentSlide === totalSlides - 1 ? 0 : currentSlide + 1"
                class="absolute right-2 top-1/2 transform -translate-y-1/2 w-8 h-8 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-primary/50"
                aria-label="Próxima imagem"
            >
                @svg('heroicon-o-chevron-right', 'w-4 h-4')
            </button>

            <!-- Slide Indicators -->
            <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex space-x-1">
                @for($i = 0; $i < $totalImages; $i++)
                    <button 
                        type="button"
                        @click="currentSlide = {{ $i }}"
                        class="w-2 h-2 rounded-full transition-all duration-200 focus:outline-none focus:ring-1 focus:ring-primary/50"
                        :class="currentSlide === {{ $i }} ? 'bg-primary' : 'bg-white/50 hover:bg-white/75'"
                        aria-label="Ir para imagem {{ $i + 1 }}"
                    ></button>
                @endfor
            </div>

            <!-- Image Counter -->
            <div class="absolute top-2 right-2 bg-black/50 text-white text-xs px-2 py-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                <span x-text="currentSlide + 1"></span>/<span>{{ $totalImages }}</span>
            </div>
        @endif
    </div>

    <!-- Keyboard Navigation -->
    @if($totalImages > 1)
        <div 
            @keydown.arrow-left.prevent="currentSlide = currentSlide === 0 ? totalSlides - 1 : currentSlide - 1"
            @keydown.arrow-right.prevent="currentSlide = currentSlide === totalSlides - 1 ? 0 : currentSlide + 1"
            @keydown.home.prevent="currentSlide = 0"
            @keydown.end.prevent="currentSlide = totalSlides - 1"
            tabindex="0"
            class="sr-only"
            aria-label="Use as setas do teclado para navegar pelas imagens"
        ></div>
    @endif
</div>
@endif 