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
        
        // Default options for Maré location
        this.options = {
            center: [-22.8666, -43.2338], // Maré coordinates
            zoom: 14,
            maxZoom: 19,
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
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
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
        });
        
        // Map events
        this.map.on('zoomend', () => {
            console.log('Zoom level:', this.map.getZoom());
        });
        
        this.map.on('moveend', () => {
            console.log('Map center:', this.map.getCenter());
        });
    }
    
    updateMarkers(results) {
        // Clear existing markers
        this.markerLayer.clearLayers();
        this.markers.clear();
        
        if (!results || results.length === 0) {
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
        const icon = this.createAccessibilityIcon(accessibility_level);
        
        // Create marker
        const marker = L.marker([latitude, longitude], { icon })
            .bindPopup(this.createPopupContent(location))
            .addTo(this.markerLayer);
        
        // Store marker reference
        this.markers.set(id, marker);
        
        // Add click event
        marker.on('click', () => {
            this.onMarkerClick(location);
        });
        
        return marker;
    }
    
    createAccessibilityIcon(accessibilityLevel) {
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
        
        return L.divIcon({
            className: 'custom-marker',
            html: `<div style="
                width: 20px;
                height: 20px;
                border-radius: 50%;
                background-color: ${color};
                border: 2px solid white;
                box-shadow: 0 2px 4px rgba(0,0,0,0.3);
            "></div>`,
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });
    }
    
    createPopupContent(location) {
        const { name, address, accessibility_level } = location;
        
        const accessibilityText = {
            'acessivel': 'Acessível',
            'parcial_acessivel': 'Parcialmente Acessível',
            'nao_acessivel': 'Não Acessível'
        };
        
        const accessibilityColor = {
            'acessivel': '#10B981',
            'parcial_acessivel': '#F59E0B',
            'nao_acessivel': '#EF4444'
        };
        
        return `
            <div class="p-2 min-w-48">
                <h3 class="font-semibold text-gray-900 mb-1">${name}</h3>
                <p class="text-sm text-gray-600 mb-2">${address}</p>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 rounded-full" style="background-color: ${accessibilityColor[accessibility_level]}"></div>
                    <span class="text-sm text-gray-700">${accessibilityText[accessibility_level]}</span>
                </div>
            </div>
        `;
    }
    
    onMarkerClick(location) {
        console.log('Marker clicked:', location);
        // Dispatch custom event that can be listened to by other components
        document.dispatchEvent(new CustomEvent('marker-clicked', {
            detail: location
        }));
    }
    
    // Public methods
    setView(lat, lng, zoom = null) {
        this.map.setView([lat, lng], zoom || this.map.getZoom());
    }
    
    getMap() {
        return this.map;
    }
    
    destroy() {
        if (this.map) {
            this.map.remove();
        }
    }
}

// Export for use in other modules
window.MapComponent = MapComponent;

// Global initialization function
window.initializeMap = function(containerId, options = {}) {
    return new MapComponent(containerId, options);
}; 