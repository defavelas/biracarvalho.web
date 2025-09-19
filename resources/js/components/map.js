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
            ...options,
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
            attributionControl: true,
        });

        L.control
            .zoom({
                position: "bottomright",
            })
            .addTo(this.map);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 18,
        }).addTo(this.map);

        this.markerLayer = L.layerGroup().addTo(this.map);

        this.setupEventListeners();
    }

    setupEventListeners() {
        document.addEventListener("livewire:init", () => {
            Livewire.on("results-updated", (event) => {
                this.updateMarkers(event.results);
            });

            Livewire.on("focus-location", (event) => {
                this.focusLocation(event.locationId);
            });
        });

        // Load initial data from API
        this.loadMapData();

        this.map.on("zoomend", () => {});

        this.map.on("moveend", () => {});

        this.map.on("click", (e) => {
            if (!e.originalEvent.defaultPrevented) {
                this.closeMapCard();
            }
        });

        // Keyboard navigation for map
        this.map.getContainer().setAttribute("tabindex", "0");
        this.map.getContainer().setAttribute("role", "application");
        this.map
            .getContainer()
            .setAttribute("aria-label", "Mapa interativo de acessibilidade - Use as setas para navegar");

        this.map.getContainer().addEventListener("keydown", (e) => {
            this.handleMapKeydown(e);
        });
    }

    handleMapKeydown(e) {
        const panDistance = 100; // pixels to pan
        const zoomStep = 1;

        switch (e.code) {
            case "ArrowUp":
                e.preventDefault();
                this.map.panBy([0, -panDistance]);
                break;
            case "ArrowDown":
                e.preventDefault();
                this.map.panBy([0, panDistance]);
                break;
            case "ArrowLeft":
                e.preventDefault();
                this.map.panBy([-panDistance, 0]);
                break;
            case "ArrowRight":
                e.preventDefault();
                this.map.panBy([panDistance, 0]);
                break;
            case "Equal":
            case "NumpadAdd":
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    this.map.zoomIn(zoomStep);
                }
                break;
            case "Minus":
            case "NumpadSubtract":
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    this.map.zoomOut(zoomStep);
                }
                break;
            case "Tab":
                this.cycleMarkers(e.shiftKey);
                break;
            case "Enter":
            case "Space":
                if (this.selectedLocationId) {
                    e.preventDefault();
                    const location = this.getLocationById(this.selectedLocationId);
                    if (location) {
                        this.onMarkerClick(location);
                    }
                }
                break;
            case "Escape":
                e.preventDefault();
                this.closeMapCard();
                break;
        }
    }

    cycleMarkers(reverse = false) {
        const markerIds = Array.from(this.markers.keys());
        if (markerIds.length === 0) return;

        let currentIndex = -1;
        if (this.selectedLocationId) {
            currentIndex = markerIds.indexOf(this.selectedLocationId);
        }

        let nextIndex;
        if (reverse) {
            nextIndex = currentIndex > 0 ? currentIndex - 1 : markerIds.length - 1;
        } else {
            nextIndex = currentIndex < markerIds.length - 1 ? currentIndex + 1 : 0;
        }

        const nextLocationId = markerIds[nextIndex];
        const marker = this.markers.get(nextLocationId);

        if (marker) {
            this.selectedLocationId = nextLocationId;
            this.updateMarkerStyles();
            this.map.setView(marker.getLatLng(), Math.max(this.map.getZoom(), 16));

            // Announce to screen readers
            this.announceLocation(marker.locationData);
        }
    }

    announceLocation(location) {
        // Create or update live region for screen reader announcements
        let liveRegion = document.getElementById("map-live-region");
        if (!liveRegion) {
            liveRegion = document.createElement("div");
            liveRegion.id = "map-live-region";
            liveRegion.setAttribute("aria-live", "polite");
            liveRegion.setAttribute("aria-atomic", "true");
            liveRegion.className = "sr-only";
            document.body.appendChild(liveRegion);
        }

        liveRegion.textContent = `Focalizando ${location.name}. ${location.typeLabel}. Pressione Enter para abrir detalhes.`;
    }

    async loadMapData() {
        try {
            const response = await fetch("/api/locations/map");
            if (!response.ok) {
                throw new Error("Failed to load map data");
            }

            const locations = await response.json();
            this.updateMarkers(locations);
        } catch (error) {
            console.error("Error loading map data:", error);
        }
    }

    updateMarkers(results) {
        this.markerLayer.clearLayers();
        this.markers.clear();

        if (!results || results.length === 0) {
            this.closeMapCard();
            return;
        }

        results.forEach((result) => {
            this.addMarker(result);
        });

        if (results.length > 0) {
            const group = new L.featureGroup(Array.from(this.markers.values()));
            this.map.fitBounds(group.getBounds().pad(0.1));
        }
    }

    addMarker(location) {
        const { id, name, type, latitude, longitude, typeColor } = location;

        const locationId = String(id);

        const icon = this.createAccessibilityIcon(type, typeColor, locationId === this.selectedLocationId);

        const marker = L.marker([latitude, longitude], { icon }).addTo(this.markerLayer);

        marker.locationData = location;

        this.markers.set(locationId, marker);

        marker.on("click", (e) => {
            e.originalEvent.preventDefault();
            this.onMarkerClick(location);
        });

        return marker;
    }

    createAccessibilityIcon(type, typeColor, isSelected = false) {
        let iconUrl = "/assets/images/accessible.png"; // default

        switch (type) {
            case "accessible":
                iconUrl = "/assets/images/accessible.png";
                break;
            case "non_accessible":
                iconUrl = "/assets/images/non_accessible.png";
                break;
        }

        return L.icon({
            iconUrl: iconUrl,
            iconSize: [34, 46],
            iconAnchor: [17, 46],
            popupAnchor: [0, -46],
            className: isSelected ? "selected-marker" : "accessibility-marker",
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
            console.error("Map container not found:", this.containerId);
            return;
        }

        const cardHtml = this.createMapCardHTML(location);
        const isMobile = window.innerWidth < 768;

        mapContainer.insertAdjacentHTML("afterend", cardHtml);

        // Only add external close buttons for desktop
        if (!isMobile) {
            const closeButtonHtml = this.createCloseButtonHTML(location);
            mapContainer.insertAdjacentHTML("afterend", closeButtonHtml);
            this.currentCloseButton = document.getElementById("map-card-buttons");
        }

        this.currentMapCard = document.getElementById("map-card-container");

        if (this.currentMapCard) {
            // Use requestAnimationFrame to ensure DOM is fully updated before positioning
            requestAnimationFrame(() => {
                this.positionMapCard(location);
            });
        } else {
            console.error("Map card container not found after insertion");
        }

        // Initialize Alpine for the map card (Livewire provides Alpine)
        if (this.currentMapCard) {
            // Wait for Livewire's Alpine to be available and force initialization
            const initAlpine = () => {
                if (typeof Alpine !== "undefined" && Alpine.initTree) {
                    try {
                        Alpine.initTree(this.currentMapCard);
                        console.log("Alpine initialized for map card");

                        // Debug: check if elements are properly initialized
                        const imageSlideshow = this.currentMapCard.querySelector('[x-data*="currentSlide"]');
                        const faqSection = this.currentMapCard.querySelector('[x-data*="openFaq"]');

                        if (imageSlideshow) {
                            console.log("Image slideshow found and should be working");
                        }
                        if (faqSection) {
                            console.log("FAQ section found and should be working");
                        }
                    } catch (error) {
                        console.error("Alpine initialization error:", error);
                    }
                } else {
                    console.warn("Alpine not available yet, retrying...");
                    setTimeout(initAlpine, 100);
                }
            };

            // Give Livewire time to load Alpine
            requestAnimationFrame(initAlpine);
        }
    }

    positionMapCard(location) {
        if (!this.currentMapCard) {
            console.error("No map card to position");
            return;
        }

        try {
            const locationKey = String(location.id);
            const marker = this.markers.get(locationKey);
            const isMobile = window.innerWidth < 768;

            if (marker) {
                const markerLatLng = marker.getLatLng();
                const markerPixel = this.map.latLngToContainerPoint(markerLatLng);

                const mapRect = this.map.getContainer().getBoundingClientRect();
                const cardWidth = isMobile ? Math.min(mapRect.width - 32, 420) : 420;
                const cardHeight = 360;

                let left = markerPixel.x;
                let top = markerPixel.y - cardHeight - 24;

                if (isMobile) {
                    // Center horizontally on mobile
                    left = (mapRect.width - cardWidth) / 2;
                    // Position at bottom of screen on mobile
                    top = mapRect.height - 400;
                } else {
                    if (left + cardWidth > mapRect.width) {
                        left = mapRect.width - cardWidth - 24;
                    }
                    if (left < 24) {
                        left = 24;
                    }
                    if (top < 24) {
                        top = markerPixel.y + 48;
                    }
                    if (top + cardHeight > mapRect.height) {
                        top = mapRect.height - cardHeight - 24;
                    }
                }

                this.currentMapCard.style.position = "absolute";
                this.currentMapCard.style.left = `${left}px`;
                this.currentMapCard.style.top = `${top}px`;
                this.currentMapCard.style.transform = "none";
                this.currentMapCard.style.display = "block";
                this.currentMapCard.style.zIndex = "1002";

                if (!isMobile && this.currentCloseButton) {
                    const mapContainerRect = this.map.getContainer().getBoundingClientRect();
                    const buttonLeft = mapContainerRect.left + left + cardWidth + 8;
                    const buttonTop = mapContainerRect.top + top;

                    this.currentCloseButton.style.position = "fixed";
                    this.currentCloseButton.style.left = `${buttonLeft}px`;
                    this.currentCloseButton.style.top = `${buttonTop}px`;
                    this.currentCloseButton.style.display = "flex";
                    this.currentCloseButton.style.zIndex = "1003";
                }
            } else {
                this.fallbackCenterPosition();
            }
        } catch (error) {
            console.error("Error positioning card:", error);
            this.fallbackCenterPosition();
        }
    }

    fallbackCenterPosition() {
        const mapRect = this.map.getContainer().getBoundingClientRect();
        const cardLeft = mapRect.width / 2 - 200;
        const cardTop = mapRect.height / 2 - 150;

        this.currentMapCard.style.left = `${cardLeft}px`;
        this.currentMapCard.style.top = `${cardTop}px`;
        this.currentMapCard.style.display = "block";

        if (this.currentCloseButton) {
            const mapContainerRect = this.map.getContainer().getBoundingClientRect();
            const buttonLeft = mapContainerRect.left + cardLeft + 405;
            const buttonTop = mapContainerRect.top + cardTop;

            console.log("Fallback positioning buttons:", {
                buttonLeft,
                buttonTop,
                mapRect: mapContainerRect,
                cardLeft,
                cardTop,
            });

            this.currentCloseButton.style.position = "fixed";
            this.currentCloseButton.style.left = `${buttonLeft}px`;
            this.currentCloseButton.style.top = `${buttonTop}px`;
            this.currentCloseButton.style.display = "flex";
            this.currentCloseButton.style.zIndex = "1003";
        }
    }

    createMapCardHTML(location) {
        const images = location.images || [];
        const hasImages = images.length > 0;
        const infos = location.infos || [];
        const hasInfos = infos.length > 0;

        let imagesHtml = "";
        if (hasImages) {
            const maxImages = Math.min(images.length, 5);
            imagesHtml = `
                <div class="mb-3 md:mb-4 relative" x-data="{ currentSlide: 0, totalSlides: ${maxImages} }">
                    <div class="relative h-40 md:h-48 bg-black/25 rounded-lg overflow-hidden group">
                        ${images
                            .slice(0, maxImages)
                            .map(
                                (image, index) => `
                            <div
                                class="absolute inset-0 transition-opacity duration-150"
                                x-show="currentSlide === ${index}"
                            >
                                <img
                                    src="${image.url}"
                                    alt="${location.name} - Imagem ${index + 1} de ${maxImages}"
                                    class="w-full h-full object-cover"
                                    loading="lazy"
                                >
                            </div>
                        `,
                            )
                            .join("")}

                        ${
                            maxImages > 1
                                ? `
                            <button
                                type="button"
                                @click="currentSlide = currentSlide === 0 ? totalSlides - 1 : currentSlide - 1"
                                class="absolute left-2 top-1/2 transform -translate-y-1/2 w-10 h-10 md:w-8 md:h-8 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-200 cursor-pointer"
                                aria-label="Imagem anterior"
                            >
                                <svg class="w-5 h-5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <button
                                type="button"
                                @click="currentSlide = currentSlide === totalSlides - 1 ? 0 : currentSlide + 1"
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 w-10 h-10 md:w-8 md:h-8 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-200 cursor-pointer"
                                aria-label="Próxima imagem"
                            >
                                <svg class="w-5 h-5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                            <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex space-x-1">
                                ${Array.from(
                                    { length: maxImages },
                                    (_, i) => `
                                    <button
                                        type="button"
                                        @click="currentSlide = ${i}"
                                        class="w-3 h-3 md:w-2 md:h-2 rounded-full transition-all duration-200 cursor-pointer"
                                        :class="currentSlide === ${i} ? 'bg-primary' : 'bg-white/50 hover:bg-white/75'"
                                        aria-label="Ir para imagem ${i + 1}"
                                    ></button>
                                `,
                                ).join("")}
                            </div>
                        `
                                : ""
                        }
                    </div>
                </div>
            `;
        }

        // Detect if mobile
        const isMobile = window.innerWidth < 768;

        return `
            <div id="map-card-container">
                <div
                    id="map-card"
                    class="w-full md:w-[420px] max-w-[420px] max-h-[80vh] bg-white rounded-lg shadow-xl pb-2 border-4 border-secondary"
                    role="dialog"
                    aria-labelledby="map-card-title"
                    aria-describedby="map-card-description"
                >
                    ${
                        hasImages
                            ? `
                    <div class="p-2 pb-0 mobile-compact-spacing relative">
                        <span class="text-left inline-block px-4 py-0.5 rounded-full shadow text-sm absolute top-4 left-4 z-50 text-white" style="background-color: ${location.typeColor}">
                            ${location.typeLabel}
                        </span>
                        ${imagesHtml}
                    </div>
                    `
                            : ""
                    }
                    <div class="px-4 py-3">
                        ${
                            !hasImages
                                ? `
                        <span class="inline-block px-3 py-1 rounded-full bg-${location.typeColor}-500 shadow text-xs text-white mb-3">
                            ${location.typeLabel}
                        </span>
                        `
                                : ""
                        }
                        <div class="flex items-center justify-between">
                            <h3 id="map-card-title" class="text-lg font-semibold text-primary leading-tight mb-1">
                                ${location.name}
                            </h3>
                            ${
                                isMobile
                                    ? `
                            <button
                                type="button"
                                onclick="closeMapCard()"
                                class="md:hidden flex-shrink-0 ml-2 w-8 h-8 rounded-full bg-secondary flex items-center justify-center"
                                aria-label="Fechar detalhes"
                            >
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                            `
                                    : ""
                            }
                        </div>
                        <p class="text-sm text-primary/80 mb-2">
                            ${location.description || "Sem descrição disponível"}
                        </p>
                        <div class="text-xs text-primary/80 leading-tight mb-4 flex justify-between items-center">
                            <span class="text-left">
                                ${location.authors ? `Contribuição ${location.authors}` : "Contribuição anônima"}
                            </span>
                            <span class="text-right">
                                ${location.createdAt || ""}
                            </span>
                        </div>
                        ${
                            hasInfos
                                ? `
                            <div>
                                <strong class="font-semibold text-primary block mb-2">Informações do local</strong>
                                <div class="max-h-48 overflow-y-auto soft-scrollbar" x-data="{ openFaq: null }">
                                    ${infos
                                        .map(
                                            (info, index) => `
                                        <div>
                                            <button
                                                type="button"
                                                @click="openFaq = openFaq === ${index} ? null : ${index}"
                                                class="w-full py-2 text-left flex items-center justify-between focus:outline-none cursor-pointer"
                                            >
                                                <span class="text-sm font-semibold text-primary">${info.title}</span>
                                                <svg
                                                    class="w-4 h-4 text-primary/70 transition-transform duration-200"
                                                    :class="{ 'rotate-180': openFaq === ${index} }"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                            <div
                                                x-show="openFaq === ${index}"
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="opacity-0"
                                                x-transition:enter-end="opacity-100"
                                                x-transition:leave="transition ease-in duration-100"
                                                x-transition:leave-start="opacity-100"
                                                x-transition:leave-end="opacity-0"
                                                id="faq-content-${index}"
                                                class="overflow-hidden"
                                            >
                                                <p class="text-sm text-primary/80 leading-relaxed pb-2">${info.value}</p></div>
                                        </div>
                                    `,
                                        )
                                        .join("")}
                                </div>
                            </div>
                        `
                                : ""
                        }
                     </div>
                 </div>
             </div>
         `;
    }

    createCloseButtonHTML(location) {
        return `
            <div id="map-card-buttons" class="flex flex-col gap-2" style="position: absolute; z-index: 1003; display: flex; flex-direction: column; gap: 8px;">
                <button
                    id="map-card-close-button"
                    type="button"
                    onclick="closeMapCard()"
                    style="
                        width: 48px;
                        height: 48px;
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
                        width="22"
                        height="22"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="#653089"
                        style="
                            stroke-width: 2;
                            stroke-linecap: round;
                            stroke-linejoin: round;
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
                        width: 48px;
                        height: 48px;
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
                        width="22"
                        height="22"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="#653089"
                        style="
                            stroke-width: 2;
                            stroke-linecap: round;
                            stroke-linejoin: round;
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
                        width: 48px;
                        height: 48px;
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
                        width="22"
                        height="22"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="#653089"
                        style="
                            stroke-width: 2;
                            stroke-linecap: round;
                            stroke-linejoin: round;
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
                const newIcon = this.createAccessibilityIcon(
                    location.type,
                    location.typeColor,
                    locationId === this.selectedLocationId,
                );
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
            console.warn(
                "Marker not found for location ID:",
                locationId,
                "Available markers:",
                Array.from(this.markers.keys()),
            );
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
    document.dispatchEvent(
        new CustomEvent("highlight-sidebar-location", {
            detail: { locationId },
        }),
    );
};

window.MapComponent = MapComponent;

window.initializeMap = function (containerId, options = {}) {
    const instance = new MapComponent(containerId, options);

    window.mapComponentInstance = instance;

    return instance;
};
