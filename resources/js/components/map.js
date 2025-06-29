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

        this.options = {
            center: [-22.8666, -43.2338],
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

        this.map = L.map(this.containerId, {
            center: this.options.center,
            zoom: this.options.zoom,
            maxZoom: this.options.maxZoom,
            minZoom: this.options.minZoom,
            zoomControl: false,
            attributionControl: true
        });

        L.control.zoom({
            position: 'bottomright'
        }).addTo(this.map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 18
        }).addTo(this.map);

        this.markerLayer = L.layerGroup().addTo(this.map);

        this.setupEventListeners();
    }

    setupEventListeners() {
        document.addEventListener('livewire:init', () => {
            Livewire.on('results-updated', (event) => {
                this.updateMarkers(event.results);
            });

            Livewire.on('focus-location', (event) => {
                this.focusLocation(event.locationId);
            });
        });

        this.map.on('zoomend', () => {
        });

        this.map.on('moveend', () => {
        });

        this.map.on('click', (e) => {
            if (!e.originalEvent.defaultPrevented) {
                this.closeMapCard();
            }
        });
    }

    updateMarkers(results) {
        this.markerLayer.clearLayers();
        this.markers.clear();

        if (!results || results.length === 0) {
            this.closeMapCard();
            return;
        }

        results.forEach(result => {
            this.addMarker(result);
        });

        if (results.length > 0) {
            const group = new L.featureGroup(Array.from(this.markers.values()));
            this.map.fitBounds(group.getBounds().pad(0.1));
        }
    }

    addMarker(location) {
        const { id, name, address, accessibility_level, latitude, longitude } = location;

        const locationId = String(id);

        const icon = this.createAccessibilityIcon(accessibility_level, locationId === this.selectedLocationId);

        const marker = L.marker([latitude, longitude], { icon })
            .addTo(this.markerLayer);

        marker.locationData = location;

        this.markers.set(locationId, marker);

        marker.on('click', (e) => {
            e.originalEvent.preventDefault();
            this.onMarkerClick(location);
        });

        return marker;
    }

    createAccessibilityIcon(accessibilityLevel, isSelected = false) {
        let color = '#666666';

        switch (accessibilityLevel) {
            case 'acessivel':
                color = '#10B981';
                break;
            case 'parcial_acessivel':
                color = '#F59E0B';
                break;
            case 'nao_acessivel':
                color = '#EF4444';
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
                                    border: ${borderWidth}px solid ${isSelected ? '#653089' : 'white'};
                box-shadow: 0 2px 8px rgba(0,0,0,${isSelected ? '0.5' : '0.3'});
                transform: ${isSelected ? 'scale(1.1)' : 'scale(1)'};
                transition: all 0.2s ease;
            "></div>`,
            iconSize: [size, size],
            iconAnchor: [size / 2, size / 2]
        });
    }

    onMarkerClick(location) {
        this.closeMapCard();

        this.selectedLocationId = String(location.id);
        this.updateMarkerStyles();

        const targetZoom = this.markers.size === 1 ? 18 : Math.max(this.map.getZoom(), 16);
        this.map.setView([location.latitude, location.longitude], targetZoom);

        setTimeout(() => {
            this.showMapCard(location);
        }, 100);
    }

    showMapCard(location) {
        this.closeMapCard();

        const mapContainer = document.getElementById(this.containerId);
        if (!mapContainer) {
            console.error('Map container not found:', this.containerId);
            return;
        }

        const cardHtml = this.createMapCardHTML(location);
        const closeButtonHtml = this.createCloseButtonHTML(location);

        mapContainer.insertAdjacentHTML('afterend', cardHtml);
        mapContainer.insertAdjacentHTML('afterend', closeButtonHtml);

        this.currentMapCard = document.getElementById('map-card-container');
        this.currentCloseButton = document.getElementById('map-card-buttons');

        if (this.currentMapCard) {
            // Use requestAnimationFrame to ensure DOM is fully updated before positioning
            requestAnimationFrame(() => {
                this.positionMapCard(location);
            });
        } else {
            console.error('Map card container not found after insertion');
        }

        if (typeof Alpine !== 'undefined' && this.currentMapCard) {
            Alpine.initTree(this.currentMapCard);
        }
    }

    positionMapCard(location) {
        if (!this.currentMapCard) {
            console.error('No map card to position');
            return;
        }

        try {
            const locationKey = String(location.id);
            const marker = this.markers.get(locationKey);

            if (marker) {
                const markerLatLng = marker.getLatLng();
                const markerPixel = this.map.latLngToContainerPoint(markerLatLng);

                const mapRect = this.map.getContainer().getBoundingClientRect();
                const cardWidth = 400;
                const cardHeight = 300;

                let left = markerPixel.x;
                let top = markerPixel.y - cardHeight - 20;

                if (left + cardWidth > mapRect.width) {
                    left = mapRect.width - cardWidth - 20;
                }
                if (left < 20) {
                    left = 20;
                }
                if (top < 20) {
                    top = markerPixel.y + 40;
                }
                if (top + cardHeight > mapRect.height) {
                    top = mapRect.height - cardHeight - 20;
                }

                this.currentMapCard.style.left = `${left}px`;
                this.currentMapCard.style.top = `${top}px`;
                this.currentMapCard.style.transform = 'none';
                this.currentMapCard.style.display = 'block';

                if (this.currentCloseButton) {
                    const mapContainerRect = this.map.getContainer().getBoundingClientRect();
                    const buttonLeft = mapContainerRect.left + left + cardWidth + 5;
                    const buttonTop = mapContainerRect.top + top;

                    console.log('Positioning buttons:', {
                        buttonLeft,
                        buttonTop,
                        mapRect: mapContainerRect,
                        cardLeft: left,
                        cardTop: top,
                        cardWidth
                    });

                    this.currentCloseButton.style.position = 'fixed';
                    this.currentCloseButton.style.left = `${buttonLeft}px`;
                    this.currentCloseButton.style.top = `${buttonTop}px`;
                    this.currentCloseButton.style.display = 'flex';
                    this.currentCloseButton.style.zIndex = '1003';
                }
            } else {
                this.fallbackCenterPosition();
            }
        } catch (error) {
            console.error('Error positioning card:', error);
            this.fallbackCenterPosition();
        }
    }

    fallbackCenterPosition() {
        const mapRect = this.map.getContainer().getBoundingClientRect();
        const cardLeft = mapRect.width / 2 - 200;
        const cardTop = mapRect.height / 2 - 150;

        this.currentMapCard.style.left = `${cardLeft}px`;
        this.currentMapCard.style.top = `${cardTop}px`;
        this.currentMapCard.style.display = 'block';

        if (this.currentCloseButton) {
            const mapContainerRect = this.map.getContainer().getBoundingClientRect();
            const buttonLeft = mapContainerRect.left + cardLeft + 405;
            const buttonTop = mapContainerRect.top + cardTop;

            console.log('Fallback positioning buttons:', {
                buttonLeft,
                buttonTop,
                mapRect: mapContainerRect,
                cardLeft,
                cardTop
            });

            this.currentCloseButton.style.position = 'fixed';
            this.currentCloseButton.style.left = `${buttonLeft}px`;
            this.currentCloseButton.style.top = `${buttonTop}px`;
            this.currentCloseButton.style.display = 'flex';
            this.currentCloseButton.style.zIndex = '1003';
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
            'nao_acessivel': 'bg-accent-orange'
        };

        const images = location.images || [];
        const hasImages = images.length > 0;
        const description = location.description || [];

        let imagesHtml = '';
        if (hasImages) {
            const maxImages = Math.min(images.length, 5);
            imagesHtml = `
                <div class="mb-4 relative" x-data="{ currentSlide: 0, totalSlides: ${maxImages} }">
                    <div class="relative h-48 bg-black/25 rounded-lg overflow-hidden group">
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
                                ${Array.from({ length: maxImages }, (_, i) => `
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
                <div class="mb-4 h-48 bg-black/25 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-primary/50 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-sm text-primary/50">Sem imagens disponíveis</span>
                </div>
            `;
        }

        return `
            <div id="map-card-container">
                                <div 
                    id="map-card" 
                    class="w-96 max-w-[calc(100vw-2rem)] sm:max-w-96 bg-secondary border-4 border-black/15 rounded-lg shadow-xl"
                    role="dialog"
                    aria-labelledby="map-card-title"
                    aria-describedby="map-card-description"
                >
                    <div class="p-2">
                    ${imagesHtml}

                    <div class="space-y-2">
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
                            <p id="map-card-description" class="text-sm text-primary/80 leading-relaxed">
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
                                <span class="text-xs text-primary/60 font-mono">
                                    ${parseFloat(location.latitude).toFixed(6)}, ${parseFloat(location.longitude).toFixed(6)}
                                </span>
                            </div>
                        ` : ''}

                        ${description.length > 0 ? `
                            <div class="border-t border-white/10 pt-3">
                                <h4 class="text-sm font-semibold text-primary mb-2">Sobre este local</h4>
                                <div class="max-h-80 overflow-y-auto soft-scrollbar space-y-3">
                                    ${description.map(paragraph => `
                                        <p class="text-sm text-primary/90 leading-relaxed">${paragraph}</p>
                                    `).join('')}
                                </div>
                            </div>
                        ` : ''}
                     </div>
                 </div>
             </div>
         </div>
         `;
    }

    createCloseButtonHTML(location) {
        return `
            <div id="map-card-buttons" style="position: absolute; z-index: 1003; display: flex; flex-direction: column; gap: 8px;">
                <button 
                    id="map-card-close-button"
                    type="button" 
                    onclick="closeMapCard()"
                    style="
                        width: 44px;
                        height: 44px;
                        border-radius: 50%;
                        background-color: #CED842;
                        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
                        cursor: pointer;
                        transition: all 300ms ease-in-out;
                        border: none;
                        padding: 0;
                        margin: 0;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    "
                    onmouseover="this.style.boxShadow='0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)'; this.style.transform='translateY(-2px)';"
                    onmouseout="this.style.boxShadow='0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)'; this.style.transform='translateY(0)';"
                    aria-label="Fechar detalhes do local"
                >
                    <svg 
                        width="20" 
                        height="20" 
                        viewBox="0 0 24 24" 
                        fill="none" 
                        stroke="#653089" 
                        style="
                            stroke-width: 2;
                            stroke-linecap: round;
                            stroke-linejoin: round;
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                        "
                    >
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>

                <button 
                    type="button" 
                    onclick="centerMapOnLocation(${location.latitude}, ${location.longitude})"
                    style="
                        width: 44px;
                        height: 44px;
                        border-radius: 50%;
                        background-color: #CED842;
                        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
                        cursor: pointer;
                        transition: all 300ms ease-in-out;
                        border: none;
                        padding: 0;
                        margin: 0;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    "
                    onmouseover="this.style.boxShadow='0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)'; this.style.transform='translateY(-2px)';"
                    onmouseout="this.style.boxShadow='0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)'; this.style.transform='translateY(0)';"
                    aria-label="Centralizar no mapa"
                >
                    <svg 
                        width="20" 
                        height="20" 
                        viewBox="0 0 24 24" 
                        fill="none" 
                        stroke="#653089" 
                        style="
                            stroke-width: 2;
                            stroke-linecap: round;
                            stroke-linejoin: round;
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                        "
                    >
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                </button>

                <button 
                    type="button" 
                    onclick="highlightLocationInSidebar('${location.id}')"
                    style="
                        width: 44px;
                        height: 44px;
                        border-radius: 50%;
                        background-color: #CED842;
                        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
                        cursor: pointer;
                        transition: all 300ms ease-in-out;
                        border: none;
                        padding: 0;
                        margin: 0;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    "
                    onmouseover="this.style.boxShadow='0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)'; this.style.transform='translateY(-2px)';"
                    onmouseout="this.style.boxShadow='0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)'; this.style.transform='translateY(0)';"
                    aria-label="Ver na lista"
                >
                    <svg 
                        width="20" 
                        height="20" 
                        viewBox="0 0 24 24" 
                        fill="none" 
                        stroke="#653089" 
                        style="
                            stroke-width: 2;
                            stroke-linecap: round;
                            stroke-linejoin: round;
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                        "
                    >
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
            </div>
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
        this.closeMapCard();

        const locationKey = String(locationId);
        const marker = this.markers.get(locationKey);

        if (marker && marker.locationData) {
            const location = marker.locationData;

            this.selectedLocationId = locationKey;
            this.updateMarkerStyles();

            const targetZoom = this.markers.size === 1 ? 18 : Math.max(this.map.getZoom(), 16);
            this.map.setView(marker.getLatLng(), targetZoom);

            setTimeout(() => {
                this.showMapCard(location);
            }, 100);
        } else {
            console.warn('Marker not found for location ID:', locationId, 'Available markers:', Array.from(this.markers.keys()));
        }
    }

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

window.closeMapCard = function () {
    const mapComponent = window.mapComponentInstance;
    if (mapComponent) {
        mapComponent.closeMapCard();
    }
};

window.centerMapOnLocation = function (lat, lng) {
    const mapComponent = window.mapComponentInstance;
    if (mapComponent) {
        mapComponent.setView(lat, lng, 18);
    }
};

window.highlightLocationInSidebar = function (locationId) {
    document.dispatchEvent(new CustomEvent('highlight-sidebar-location', {
        detail: { locationId }
    }));
};

window.MapComponent = MapComponent;

window.initializeMap = function (containerId, options = {}) {
    const instance = new MapComponent(containerId, options);

    window.mapComponentInstance = instance;

    return instance;
}; 