@props([
    'location' => null,
    'show' => false
])

@if($location && $show)
<div 
    id="map-card" 
    class="absolute bottom-4 left-1/2 transform -translate-x-1/2 w-80 bg-secondary border-4 border-black/15 rounded-lg shadow-xl z-[200] transition-all duration-300 ease-in-out flex flex-col max-h-[70vh]"
    role="dialog"
    aria-labelledby="map-card-title"
    aria-describedby="map-card-description"
>
    <!-- Close Button -->
    <button 
        type="button" 
        onclick="closeMapCard()"
        class="absolute -top-2 -right-2 w-8 h-8 bg-primary text-secondary rounded-full flex items-center justify-center shadow-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-colors duration-200 z-10"
        aria-label="Fechar detalhes do local"
    >
        @svg('heroicon-o-x-mark', 'w-4 h-4')
    </button>

    <!-- Fixed Header -->
    <div class="p-4 border-b border-primary/20 flex-shrink-0">
        <!-- Title and Accessibility Badge -->
        <div class="flex items-start justify-between space-x-3 mb-3">
            <div class="flex-1">
                <h3 id="map-card-title" class="text-[13px] md:text-lg font-medium md:font-semibold text-primary leading-tight">
                    {{ $location['name'] }}
                </h3>
            </div>
            <span class="inline-flex items-center px-1.5 py-0.5 md:px-2.5 md:py-1 font-normal md:font-semibold rounded-full text-[9px] md:text-xs text-black/75 flex-shrink-0
                {{ $location['accessibility_level'] === 'acessivel' ? 'bg-green-500' : '' }}
                {{ $location['accessibility_level'] === 'parcial_acessivel' ? 'bg-yellow-500' : '' }}
                {{ $location['accessibility_level'] === 'nao_acessivel' ? 'bg-accent-orange' : '' }}">
                {{ $location['accessibility_level'] === 'acessivel' ? 'Acessível' : '' }}
                {{ $location['accessibility_level'] === 'parcial_acessivel' ? 'Parcialmente Acessível' : '' }}
                {{ $location['accessibility_level'] === 'nao_acessivel' ? 'Não Acessível' : '' }}
            </span>
        </div>

        <!-- Address -->
        <div class="flex items-start space-x-2 mb-3">
            <div class="w-3.5 h-3.5 md:w-5 md:h-5 text-primary flex-shrink-0 mt-0.5">
                @svg('heroicon-o-map-pin', 'w-3.5 h-3.5 md:w-5 md:h-5')
            </div>
            <p id="map-card-description" class="text-[11px] md:text-sm text-white/80 leading-snug md:leading-relaxed">
                {{ $location['address'] }}
            </p>
        </div>

        <!-- Coordinates -->
        @if(isset($location['latitude']) && isset($location['longitude']))
            <div class="flex items-center space-x-2">
                <div class="w-3.5 h-3.5 md:w-5 md:h-5 text-primary flex-shrink-0">
                    @svg('heroicon-o-globe-alt', 'w-3.5 h-3.5 md:w-5 md:h-5')
                </div>
                <span class="text-[9.5px] md:text-xs text-white/60 font-mono"
                    aria-label="Coordenadas: Latitude {{ number_format($location['latitude'], 6) }}, Longitude {{ number_format($location['longitude'], 6) }}">
                    {{ number_format($location['latitude'], 6) }}, {{ number_format($location['longitude'], 6) }}
                </span>
            </div>
        @endif
    </div>

    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto p-4">
        <!-- Image Slideshow Section -->
        @if(isset($location['images']) && count($location['images']) > 0)
            <div class="mb-4 relative">
                <x-image-slideshow :images="$location['images']" :alt="$location['name']" />
            </div>
        @else
            <!-- Placeholder when no images -->
            <div class="mb-4 h-32 bg-black/25 rounded-lg flex items-center justify-center">
                @svg('heroicon-o-photo', 'w-8 h-8 text-white/50')
                <span class="ml-2 text-sm text-white/50">Sem imagens disponíveis</span>
            </div>
        @endif

        <!-- Additional Content Area -->
        <div class="space-y-3">
            <!-- Description or other content can go here -->
            @if(isset($location['description']))
                <div class="text-[11px] md:text-sm text-white/70 leading-relaxed">
                    {{ $location['description'] }}
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex space-x-2 pt-4 border-t border-white/10">
                <button 
                    type="button"
                    onclick="centerMapOnLocation({{ $location['latitude'] }}, {{ $location['longitude'] }})"
                    class="flex-1 bg-primary/20 hover:bg-primary/30 text-primary font-medium py-2 px-3 rounded-md transition-colors duration-200 text-sm flex items-center justify-center space-x-2 focus:outline-none focus:ring-2 focus:ring-primary/50"
                >
                    @svg('heroicon-o-map', 'w-4 h-4')
                    <span>Centralizar</span>
                </button>
                <button 
                    type="button"
                    onclick="highlightLocationInSidebar('{{ $location['id'] }}')"
                    class="flex-1 bg-primary/20 hover:bg-primary/30 text-primary font-medium py-2 px-3 rounded-md transition-colors duration-200 text-sm flex items-center justify-center space-x-2 focus:outline-none focus:ring-2 focus:ring-primary/50"
                >
                    @svg('heroicon-o-list-bullet', 'w-4 h-4')
                    <span>Ver Lista</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif 