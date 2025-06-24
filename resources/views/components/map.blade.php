@props(['id' => 'map', 'class' => ''])

<div 
    id="{{ $id }}" 
    class="w-full h-screen relative z-[1] {{ $class }}"
    {{ $attributes }}
    data-map-center-lat="-22.851860351512137"
    data-map-center-lng="-43.24313600267501"
    data-map-zoom="20"
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