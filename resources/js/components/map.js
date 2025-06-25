/**
 * Map Component for Maré Accessibility Mapping
 * Handles OpenStreetMap integration with Leaflet.js
 */

class MapComponent {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.map = null;
        this.markers = new Map();
        this.markerLayer = null;
        this.currentMapCard = null;
        this.currentCloseButton = null;
        this.selectedLocationId = null;
        
        // Default options for Maré location
        this.options = {
            center: [-22.8666, -43.2338], // Maré coordinates
            zoom: 14,
            maxZoom: 18,
            minZoom: 10,
            ...options
        };
        
        this.init();
    }
    
    init() {
        const container = document.getElementById(this.containerId);
        if (!container) {
            console.error(`Map container with ID '${this.containerId}' not found`);
            return;
        }
        
        // Initialize map
        this.map = L.map(this.containerId, {
            center: this.options.center,
            zoom: this.options.zoom,
            maxZoom: this.options.maxZoom,
            minZoom: this.options.minZoom,
            zoomControl: false, // Disable default zoom control
            attributionControl: true
        });
        
        // Add zoom control to bottom right
        L.control.zoom({
            position: 'bottomright'
        }).addTo(this.map);
        
        // Add OpenStreetMap base tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 18
        }).addTo(this.map);
        
        // Create marker layer group
        this.markerLayer = L.layerGroup().addTo(this.map);
        
        // Setup event listeners
        this.setupEventListeners();
        
        console.log('Map initialized successfully');
    }
    
    setupEventListeners() {
        // Listen for Livewire events
        document.addEventListener('livewire:init', () => {
            Livewire.on('results-updated', (event) => {
                this.updateMarkers(event.results);
            });
            
            Livewire.on('focus-location', (event) => {
                this.focusLocation(event.locationId);
            });
        });
        
        // Map events
        this.map.on('zoomend', () => {
            console.log('Zoom level:', this.map.getZoom());
        });
        
        this.map.on('moveend', () => {
            console.log('Map center:', this.map.getCenter());
        });

        // Close map card when clicking on map
        this.map.on('click', (e) => {
            if (!e.originalEvent.defaultPrevented) {
                this.closeMapCard();
            }
        });
    }
    
    updateMarkers(results) {
        // Clear existing markers
        this.markerLayer.clearLayers();
        this.markers.clear();
        
        if (!results || results.length === 0) {
            this.closeMapCard();
            return;
        }
        
        // Add new markers
        results.forEach(result => {
            this.addMarker(result);
        });
        
        // Fit map to show all markers if there are any
        if (results.length > 0) {
            const group = new L.featureGroup(Array.from(this.markers.values()));
            this.map.fitBounds(group.getBounds().pad(0.1));
        }
    }
    
    addMarker(location) {
        const { id, name, address, accessibility_level, latitude, longitude } = location;
        
        // Create custom icon based on accessibility level
        const icon = this.createAccessibilityIcon(accessibility_level, id === this.selectedLocationId);
        
        // Create marker without popup
        const marker = L.marker([latitude, longitude], { icon })
            .addTo(this.markerLayer);
        
        // Store location data in marker for easy access
        marker.locationData = location;
        
        // Store marker reference
        this.markers.set(id, marker);
        
        // Add click event
        marker.on('click', (e) => {
            e.originalEvent.preventDefault();
            this.onMarkerClick(location);
        });
        
        return marker;
    }
    
    createAccessibilityIcon(accessibilityLevel, isSelected = false) {
        let color = '#666666'; // default color
        
        switch (accessibilityLevel) {
            case 'acessivel':
                color = '#10B981'; // green
                break;
            case 'parcial_acessivel':
                color = '#F59E0B'; // yellow
                break;
            case 'nao_acessivel':
                color = '#EF4444'; // red
                break;
        }
        
        const size = isSelected ? 28 : 20;
        const borderWidth = isSelected ? 3 : 2;
        
        return L.divIcon({
            className: 'custom-marker',
            html: `<div style="
                width: ${size}px;
                height: ${size}px;
                border-radius: 50%;
                background-color: ${color};
                border: ${borderWidth}px solid ${isSelected ? '#CED842' : 'white'};
                box-shadow: 0 2px 8px rgba(0,0,0,${isSelected ? '0.5' : '0.3'});
                transform: ${isSelected ? 'scale(1.1)' : 'scale(1)'};
                transition: all 0.2s ease;
            "></div>`,
            iconSize: [size, size],
            iconAnchor: [size/2, size/2]
        });
    }
    
    onMarkerClick(location) {
        console.log('Marker clicked:', location);
        this.selectedLocationId = location.id;
        this.showMapCard(location);
        this.updateMarkerStyles();
        
        // Dispatch custom event that can be listened to by other components
        document.dispatchEvent(new CustomEvent('marker-clicked', {
            detail: location
        }));
    }
    
    showMapCard(location) {
        // Remove existing card if any
        this.closeMapCard();
        
        // Create new map card
        const mapContainer = document.getElementById(this.containerId);
        const cardHtml = this.createMapCardHTML(location);
        const closeButtonHtml = this.createCloseButtonHTML();
        
        // Insert the card and close button
        mapContainer.insertAdjacentHTML('afterend', cardHtml);
        mapContainer.insertAdjacentHTML('afterend', closeButtonHtml);
        
        this.currentMapCard = document.getElementById('map-card-container');
        this.currentCloseButton = document.getElementById('map-card-close-button');
        
        // Position card near marker or center screen
        if (this.currentMapCard) {
            this.positionMapCard(location);
        } else {
            console.error('Map card container not found after insertion');
        }
        
        // Add Alpine.js reactivity if not already present
        if (typeof Alpine !== 'undefined' && this.currentMapCard) {
            Alpine.initTree(this.currentMapCard);
        }
    }
    
        positionMapCard(location) {
        if (!this.currentMapCard) return;
        
        try {
            // Get marker position on screen
            const marker = this.markers.get(location.id);
            if (marker) {
                const markerLatLng = marker.getLatLng();
                const markerPixel = this.map.latLngToContainerPoint(markerLatLng);
                
                // Get map container dimensions
                const mapRect = this.map.getContainer().getBoundingClientRect();
                const cardWidth = 400; // Larger card width
                const cardHeight = 300; // Estimated card height
                
                let left = markerPixel.x;
                let top = markerPixel.y - cardHeight - 20; // Position above marker
                
                // Adjust if card would go off screen
                if (left + cardWidth > mapRect.width) {
                    left = mapRect.width - cardWidth - 20;
                }
                if (left < 20) {
                    left = 20;
                }
                if (top < 20) {
                    top = markerPixel.y + 40; // Position below marker if no space above
                }
                if (top + cardHeight > mapRect.height) {
                    top = mapRect.height - cardHeight - 20;
                }
                
                // Apply positioning to card
                this.currentMapCard.style.left = `${left}px`;
                this.currentMapCard.style.top = `${top}px`;
                this.currentMapCard.style.transform = 'none';
                
                // Position close button relative to card (top-right corner)
                if (this.currentCloseButton) {
                    this.currentCloseButton.style.left = `${left + cardWidth - 10}px`; // 10px offset from right edge
                    this.currentCloseButton.style.top = `${top - 10}px`; // 10px above card
                }
            }
        } catch (error) {
            // Fallback to center positioning
            console.warn('Could not position card near marker, using center positioning:', error);
            const mapRect = this.map.getContainer().getBoundingClientRect();
            const cardLeft = mapRect.width / 2 - 200; // 200 is half of card width
            const cardTop = mapRect.height / 2 - 150; // 150 is estimated half of card height
            
            this.currentMapCard.style.left = `${cardLeft}px`;
            this.currentMapCard.style.top = `${cardTop}px`;
            
            // Position close button for center positioning
            if (this.currentCloseButton) {
                this.currentCloseButton.style.left = `${cardLeft + 390}px`; // Near right edge of card
                this.currentCloseButton.style.top = `${cardTop - 10}px`; // Above card
            }
        }
    }
    
    createMapCardHTML(location) {
        const accessibilityText = {
            'acessivel': 'Acessível',
            'parcial_acessivel': 'Parcialmente Acessível',
            'nao_acessivel': 'Não Acessível'
        };
        
        const accessibilityClass = {
            'acessivel': 'bg-green-500',
            'parcial_acessivel': 'bg-yellow-500',
            'nao_acessivel': 'bg-rose-500'
        };
        
        const images = location.images || [];
        const hasImages = images.length > 0;
        
        let imagesHtml = '';
        if (hasImages) {
            const maxImages = Math.min(images.length, 5);
            imagesHtml = `
                <div class="mb-4 relative" x-data="{ currentSlide: 0, totalSlides: ${maxImages} }">
                    <div class="relative h-32 bg-black/25 rounded-lg overflow-hidden group">
                        ${images.slice(0, maxImages).map((image, index) => `
                            <div 
                                class="absolute inset-0 transition-opacity duration-300"
                                x-show="currentSlide === ${index}"
                                style="${index === 0 ? '' : 'display: none;'}"
                            >
                                <img 
                                    src="${image}" 
                                    alt="${location.name} - Imagem ${index + 1} de ${maxImages}"
                                    class="w-full h-full object-cover"
                                    loading="lazy"
                                >
                            </div>
                        `).join('')}
                        
                        ${maxImages > 1 ? `
                            <button 
                                type="button"
                                @click="currentSlide = currentSlide === 0 ? totalSlides - 1 : currentSlide - 1"
                                class="absolute left-2 top-1/2 transform -translate-y-1/2 w-8 h-8 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <button 
                                type="button"
                                @click="currentSlide = currentSlide === totalSlides - 1 ? 0 : currentSlide + 1"
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 w-8 h-8 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                            <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex space-x-1">
                                ${Array.from({length: maxImages}, (_, i) => `
                                    <button 
                                        type="button"
                                        @click="currentSlide = ${i}"
                                        class="w-2 h-2 rounded-full transition-all duration-200"
                                        :class="currentSlide === ${i} ? 'bg-primary' : 'bg-white/50 hover:bg-white/75'"
                                    ></button>
                                `).join('')}
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        } else {
            imagesHtml = `
                <div class="mb-4 h-32 bg-black/25 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white/50 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-sm text-white/50">Sem imagens disponíveis</span>
                </div>
            `;
        }
        
        return `
            <div id="map-card-container">
                <!-- Card Content -->
                                <div 
                    id="map-card" 
                    class="w-96 max-w-[calc(100vw-2rem)] sm:max-w-96 bg-secondary border-4 border-black/15 rounded-lg shadow-xl"
                    role="dialog"
                    aria-labelledby="map-card-title"
                    aria-describedby="map-card-description"
                >
                    <div class="p-4">
                    ${imagesHtml}

                    <div class="space-y-3">
                        <div class="flex items-start justify-between space-x-3">
                            <div class="flex-1">
                                <h3 id="map-card-title" class="text-lg font-semibold text-primary leading-tight">
                                    ${location.name}
                                </h3>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 font-semibold rounded-full text-xs text-black/75 flex-shrink-0 ${accessibilityClass[location.accessibility_level]}">
                                ${accessibilityText[location.accessibility_level]}
                            </span>
                        </div>

                        <div class="flex items-start space-x-2">
                            <div class="w-5 h-5 text-primary flex-shrink-0 mt-0.5">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <p id="map-card-description" class="text-sm text-white/80 leading-relaxed">
                                ${location.address}
                            </p>
                        </div>

                        ${location.latitude && location.longitude ? `
                            <div class="flex items-center space-x-2">
                                <div class="w-5 h-5 text-primary flex-shrink-0">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-xs text-white/60 font-mono">
                                    ${parseFloat(location.latitude).toFixed(6)}, ${parseFloat(location.longitude).toFixed(6)}
                                </span>
                            </div>
                        ` : ''}

                        <div class="flex space-x-2 pt-2 border-t border-white/10">
                            <button 
                                type="button"
                                onclick="centerMapOnLocation(${location.latitude}, ${location.longitude})"
                                class="flex-1 bg-primary/20 hover:bg-primary/30 text-primary font-medium py-2 px-3 rounded-md transition-colors duration-200 text-sm flex items-center justify-center space-x-2 focus:outline-none focus:ring-2 focus:ring-primary/50"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                </svg>
                                <span>Centralizar</span>
                            </button>
                            <button 
                                type="button"
                                onclick="highlightLocationInSidebar('${location.id}')"
                                class="flex-1 bg-primary/20 hover:bg-primary/30 text-primary font-medium py-2 px-3 rounded-md transition-colors duration-200 text-sm flex items-center justify-center space-x-2 focus:outline-none focus:ring-2 focus:ring-primary/50"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                                <span>Ver Lista</span>
                            </button>
                                                 </div>
                     </div>
                 </div>
             </div>
         </div>
         `;
    }
    
    createCloseButtonHTML() {
        return `
            <button 
                id="map-card-close-button"
                type="button" 
                onclick="closeMapCard()"
                class="absolute z-[1003] w-11 h-11 rounded-full bg-secondary shadow-lg border-0 cursor-pointer flex items-center justify-center transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-0.5"
                aria-label="Fechar detalhes do local"
            >
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
    }
    
    closeMapCard() {
        if (this.currentMapCard) {
            this.currentMapCard.remove();
            this.currentMapCard = null;
        }
        if (this.currentCloseButton) {
            this.currentCloseButton.remove();
            this.currentCloseButton = null;
        }
        this.selectedLocationId = null;
        this.updateMarkerStyles();
    }
    
    updateMarkerStyles() {
        // Update all marker styles to reflect selection state
        this.markers.forEach((marker, locationId) => {
            const location = this.getLocationById(locationId);
            if (location) {
                const newIcon = this.createAccessibilityIcon(location.accessibility_level, locationId === this.selectedLocationId);
                marker.setIcon(newIcon);
            }
        });
    }
    
    getLocationById(locationId) {
        const marker = this.markers.get(locationId);
        return marker ? marker.locationData : null;
    }
    
    focusLocation(locationId) {
        const marker = this.markers.get(locationId);
        if (marker) {
            const location = marker.locationData;
            this.selectedLocationId = locationId;
            this.showMapCard(location);
            this.updateMarkerStyles();
            this.map.setView(marker.getLatLng(), Math.max(this.map.getZoom(), 16));
        }
    }
    
    // Public methods
    setView(lat, lng, zoom = null) {
        this.map.setView([lat, lng], zoom || this.map.getZoom());
    }
    
    getMap() {
        return this.map;
    }
    
    destroy() {
        this.closeMapCard();
        if (this.map) {
            this.map.remove();
        }
    }
}

// Global functions for card interactions
window.closeMapCard = function() {
    const mapComponent = window.mapComponentInstance;
    if (mapComponent) {
        mapComponent.closeMapCard();
    }
};

window.centerMapOnLocation = function(lat, lng) {
    const mapComponent = window.mapComponentInstance;
    if (mapComponent) {
        mapComponent.setView(lat, lng, 18);
    }
};

window.highlightLocationInSidebar = function(locationId) {
    // Dispatch event to highlight the location in sidebar
    document.dispatchEvent(new CustomEvent('highlight-sidebar-location', {
        detail: { locationId }
    }));
};

// Export for use in other modules
window.MapComponent = MapComponent;

// Global initialization function
window.initializeMap = function(containerId, options = {}) {
    const instance = new MapComponent(containerId, options);
    window.mapComponentInstance = instance; // Store global reference
    return instance;
}; 