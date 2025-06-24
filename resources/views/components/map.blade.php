@props(['id' => 'map', 'class' => ''])

<div 
    id="{{ $id }}" 
    class="w-full h-full {{ $class }}"
    {{ $attributes }}
    data-map-center-lat="-22.8666"
    data-map-center-lng="-43.2338"
    data-map-zoom="14"
    aria-label="Mapa interativo de acessibilidade da Maré"
    role="application"
>
    <!-- Map will be initialized here by Leaflet -->
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Wait for both Leaflet and our map component to be loaded
        function initMap() {
            if (typeof L !== 'undefined' && typeof window.initializeMap === 'function') {
                window.initializeMap('{{ $id }}');
            } else {
                // Retry after a short delay
                setTimeout(initMap, 100);
            }
        }
        
        initMap();
    });
</script>
@endpush 