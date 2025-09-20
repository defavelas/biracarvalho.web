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

            if (marker && !isMobile) {
                // For desktop, use smart collision detection positioning
                const markerElement = marker.getElement();
                if (markerElement) {
                    // Apply smart collision detection positioning
                    this.applySmartPositioning(markerElement, location);
                } else {
                    this.fallbackCenterPosition();
                }
            } else if (isMobile) {
                // Use smart mobile positioning (consistent with desktop approach)
                const mapRect = this.map.getContainer().getBoundingClientRect();
                const markerElement = marker.getElement();

                if (markerElement) {
                    // Apply smart positioning for mobile too
                    this.applySmartPositioning(markerElement, location);
                } else {
                    // Mobile fallback positioning with consistent constraints
                    const cardWidth = Math.min(mapRect.width - 32, 460);
                    const left = (mapRect.width - cardWidth) / 2;
                    const top = Math.max(20, mapRect.height - 420); // Account for mobile card height

                    this.currentMapCard.style.position = "absolute";
                    this.currentMapCard.style.left = `${left}px`;
                    this.currentMapCard.style.top = `${top}px`;
                    this.currentMapCard.style.transform = "none";
                    this.currentMapCard.style.display = "block";
                    this.currentMapCard.style.zIndex = "1002";
                    this.currentMapCard.style.width = `${cardWidth}px`;
                    this.currentMapCard.style.maxHeight = "80vh"; // Consistent with CSS
                }
            } else {
                this.fallbackCenterPosition();
            }
        } catch (error) {
            console.error("Error positioning card:", error);
            this.fallbackCenterPosition();
        }
    }

    getSidebarState() {
        const sidebar = document.querySelector('aside[aria-label="Painel de pesquisa e filtros"]');
        if (!sidebar) return { isOpen: false, width: 0, right: 0 };

        const isHidden = window.innerWidth < 768; // Mobile breakpoint
        if (isHidden) return { isOpen: false, width: 0, right: 0 };

        // Check if sidebar is open by looking at transform classes
        const hasTranslateX = sidebar.classList.contains("-translate-x-full");
        const isOpen = !hasTranslateX;

        if (!isOpen) return { isOpen: false, width: 0, right: 0 };

        // Calculate sidebar dimensions when open
        const sidebarRect = sidebar.getBoundingClientRect();
        return {
            isOpen: true,
            width: sidebarRect.width,
            right: sidebarRect.right,
        };
    }

    calculateAvailableMapArea() {
        const mapRect = this.map.getContainer().getBoundingClientRect();
        const sidebarState = this.getSidebarState();

        let availableArea = {
            left: 0,
            top: 0,
            width: mapRect.width,
            height: mapRect.height,
            right: mapRect.width,
        };

        // Adjust for sidebar when open
        if (sidebarState.isOpen) {
            const sidebarOffset = sidebarState.right - mapRect.left;
            availableArea.left = Math.max(0, sidebarOffset);
            availableArea.width = Math.max(200, mapRect.width - sidebarOffset); // Minimum 200px width
            availableArea.right = availableArea.left + availableArea.width;
        }

        return { area: availableArea, sidebar: sidebarState };
    }

    applySmartPositioning(markerElement, location) {
        if (!this.currentMapCard || !markerElement) return;

        // Get all necessary measurements with sidebar awareness
        const mapRect = this.map.getContainer().getBoundingClientRect();
        const markerRect = markerElement.getBoundingClientRect();
        const { area: availableArea, sidebar: sidebarState } = this.calculateAvailableMapArea();
        const cardWidth = 460;

        // Get dynamic card height after content is rendered
        requestAnimationFrame(() => {
            const cardHeight = this.currentMapCard.offsetHeight;
            const offset = 8;
            const margin = 24; // Minimum margin from viewport edges

            // Calculate marker position relative to available area (not full map)
            const markerX = markerRect.left - mapRect.left + markerRect.width / 2;
            const markerY = markerRect.top - mapRect.top + markerRect.height;

            // Calculate available space in each direction (sidebar-aware)
            const spaceAbove = markerRect.top - mapRect.top;
            const spaceBelow = mapRect.bottom - markerRect.bottom;
            const spaceLeft = markerRect.left - mapRect.left - availableArea.left;
            const spaceRight = availableArea.right - markerRect.right;

            let finalX, finalY;
            let placement = "top"; // Default preference

            // Sidebar-aware positioning logic with preference adjustments
            const preferRight = sidebarState.isOpen; // Prefer right side when sidebar is open

            if (preferRight && spaceRight >= cardWidth + offset + margin) {
                // 1. Try positioning to the right of marker (preferred when sidebar open)
                placement = "right";
                finalX = Math.min(markerX + offset + markerRect.width / 2, availableArea.right - cardWidth - margin);
                finalY = Math.max(
                    margin,
                    Math.min(markerY - markerRect.height - cardHeight / 2, availableArea.height - cardHeight - margin),
                );
            } else if (spaceAbove >= cardHeight + offset + margin) {
                // 2. Try positioning above marker
                placement = "top";
                finalX = Math.max(
                    availableArea.left + margin,
                    Math.min(markerX - cardWidth / 2, availableArea.right - cardWidth - margin),
                );
                finalY = markerY - cardHeight - offset - markerRect.height;
            } else if (spaceBelow >= cardHeight + offset + margin) {
                // 3. Try positioning below marker
                placement = "bottom";
                finalX = Math.max(
                    availableArea.left + margin,
                    Math.min(markerX - cardWidth / 2, availableArea.right - cardWidth - margin),
                );
                finalY = markerY + offset;
            } else if (!preferRight && spaceLeft >= cardWidth + offset + margin) {
                // 4. Try positioning to the left of marker (only if sidebar closed)
                placement = "left";
                finalX = Math.max(availableArea.left + margin, markerX - cardWidth - offset - markerRect.width / 2);
                finalY = Math.max(
                    margin,
                    Math.min(markerY - markerRect.height - cardHeight / 2, availableArea.height - cardHeight - margin),
                );
            } else if (!preferRight && spaceRight >= cardWidth + offset + margin) {
                // 5. Try positioning to the right of marker (fallback)
                placement = "right";
                finalX = Math.min(markerX + offset + markerRect.width / 2, availableArea.right - cardWidth - margin);
                finalY = Math.max(
                    margin,
                    Math.min(markerY - markerRect.height - cardHeight / 2, availableArea.height - cardHeight - margin),
                );
            } else {
                // 6. Smart center positioning (sidebar-aware)
                placement = "center-available";
                finalX = Math.max(
                    availableArea.left + margin,
                    (availableArea.left + availableArea.right - cardWidth) / 2,
                );
                finalY = Math.max(margin, (availableArea.height - cardHeight) / 2);
            }

            // Apply the calculated position
            this.currentMapCard.style.left = `${finalX}px`;
            this.currentMapCard.style.top = `${finalY}px`;
            this.currentMapCard.style.position = "absolute";
            this.currentMapCard.style.display = "block";
            this.currentMapCard.style.zIndex = "150"; // Above sidebar (z-[100])

            console.log(`Card positioned ${placement}:`, {
                finalX,
                finalY,
                cardHeight,
                placement,
                sidebarOpen: sidebarState.isOpen,
                availableWidth: availableArea.width,
                sidebarOffset: sidebarState.isOpen ? availableArea.left : 0,
            });

            // Position close button relative to the card
            this.positionCloseButton(finalX, finalY, cardWidth, cardHeight);
        });
    }

    positionCloseButton(cardX, cardY, cardWidth, cardHeight) {
        if (!this.currentCloseButton) return;

        // Position buttons to the right of the card by default
        let buttonX = cardX + cardWidth + 8;
        let buttonY = cardY;

        // If buttons would go off-screen, position them inside or to the left
        const mapRect = this.map.getContainer().getBoundingClientRect();
        if (buttonX + 48 > mapRect.width) {
            // Try left side
            buttonX = cardX - 48 - 8;
            if (buttonX < 0) {
                // Position inside card at top-right
                buttonX = cardX + cardWidth - 48 - 8;
                buttonY = cardY + 8;
            }
        }

        this.currentCloseButton.style.position = "absolute";
        this.currentCloseButton.style.left = `${buttonX}px`;
        this.currentCloseButton.style.top = `${buttonY}px`;
        this.currentCloseButton.style.display = "flex";
        this.currentCloseButton.style.zIndex = "151"; // Above cards (z-150)
    }

    fallbackCenterPosition() {
        const { area: availableArea, sidebar: sidebarState } = this.calculateAvailableMapArea();

        // Smart fallback positioning (sidebar-aware)
        const cardWidth = 400;
        const cardHeight = 300;
        const cardLeft = Math.max(availableArea.left + 24, (availableArea.left + availableArea.right - cardWidth) / 2);
        const cardTop = Math.max(24, (availableArea.height - cardHeight) / 2);

        this.currentMapCard.style.left = `${cardLeft}px`;
        this.currentMapCard.style.top = `${cardTop}px`;
        this.currentMapCard.style.display = "block";
        this.currentMapCard.style.zIndex = "150"; // Above sidebar

        console.log("Fallback positioning (sidebar-aware):", {
            cardLeft,
            cardTop,
            sidebarOpen: sidebarState.isOpen,
            availableWidth: availableArea.width,
        });

        if (this.currentCloseButton) {
            this.positionCloseButton(cardLeft, cardTop, cardWidth, cardHeight);
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
                <div class="relative">
                    <div class="relative h-56 md:h-64 bg-black/25 rounded-lg overflow-hidden group">
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
                    class="w-[460px] max-w-[460px] bg-white rounded-lg shadow-xl pb-2 border-4 border-secondary flex flex-col max-h-[80vh] min-h-[300px] overflow-y-auto overflow-x-hidden relative"
                    role="dialog"
                    aria-labelledby="map-card-title"
                    aria-describedby="map-card-description"
                     x-data="{ currentSlide: 0, totalSlides: ${hasImages ? Math.min(images.length, 5) : 0}, openFaq: null, scrollToContent(accordionId) { const element = document.getElementById(accordionId); if (element) { element.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); const cardContainer = document.getElementById('map-card'); if (cardContainer) { cardContainer.scrollTop = element.offsetTop - 10; } } } }"
                >
                    <!-- Close Button (Full-rounded, external positioning) -->
                    <button
                        type="button"
                        onclick="closeMapCard()"
                        class="button-close absolute right-0 top-0 md:hidden w-12 h-12 bg-secondary shadow-lg border-4 border-white rounded-full cursor-pointer flex items-center justify-center transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-0.5 z-50"
                        aria-label="Fechar detalhes do local"
                    >
                        <svg class="w-6 h-6 text-primary transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    ${
                        hasImages
                            ? `
                    <div class="p-2 pb-0 mobile-compact-spacing relative">
                        <span class="text-left inline-block px-4 py-0.5 rounded-full shadow absolute top-4 left-4 z-50 text-white" style="background-color: ${location.typeColor}">
                            ${location.typeLabel}
                        </span>
                        ${imagesHtml}
                    </div>
                    `
                            : ""
                    }
                    <!-- Fixed Header -->
                    <div class="pt-2.5 px-2 flex-shrink-0">
                        ${
                            !hasImages
                                ? `
                        <span class="inline-block px-3 py-1 rounded-full shadow  text-white mb-3" style="background-color: ${location.typeColor}">
                            ${location.typeLabel}
                        </span>
                        `
                                : ""
                        }
                        <div class="flex items-center justify-between">
                            <h3 id="map-card-title" class="text-lg font-semibold text-primary leading-tight mb-1">
                                ${location.name}
                            </h3>

                        </div>
                        <p class="text-base text-primary/80 mb-2">
                            ${location.description || "Sem descrição disponível"}
                        </p>
                        <div class="text-sm text-primary/80 leading-tight mb-4 flex justify-between items-center">
                            <span class="text-left">
                                ${location.authors ? `Contribuição ${location.authors}` : "Contribuição anônima"}
                            </span>
                            <span class="text-right">
                                ${
                                    location.createdAt
                                        ? (() => {
                                              try {
                                                  const date = new Date(location.createdAt);
                                                  return isNaN(date.getTime())
                                                      ? location.createdAt
                                                      : date.toLocaleDateString("pt-BR");
                                              } catch {
                                                  return location.createdAt;
                                              }
                                          })()
                                        : ""
                                }
                            </span>
                        </div>

                        <!-- Accordion Section Label (Fixed in Header) -->
                        ${
                            hasInfos
                                ? `<strong class="text-base md:text-lg font-semibold text-primary block">Informações do local</strong>`
                                : ""
                        }
                    </div>

                    <!-- Content -->
                    <div class="flex-1 px-2 space-y-3">
                        ${
                            hasInfos
                                ? `
                             <div class="max-h-48 overflow-y-auto soft-scrollbar relative group" style="max-height: 12rem; overflow: hidden;">
                                     ${infos
                                         .map(
                                             (info, index) => `
                                        <div>
                                            <button
                                                type="button"
                                                 @click="openFaq = openFaq === ${index} ? null : ${index};
                                                          $nextTick(() => {
                                                            scrollToContent('faq-content-${index}');
                                                            // Force card height recalculation
                                                            const cardContainer = document.getElementById('map-card');

                                                            if (cardContainer) {
                                                              // Only scroll card if content exceeds viewport
                                                              const cardRect = cardContainer.getBoundingClientRect();
                                                              const viewportHeight = window.innerHeight;
                                                              if (cardRect.bottom > viewportHeight) {
                                                                cardContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                                              }
                                                            }
                                                          })"
                                                class="w-full py-2 text-left flex items-center justify-between focus:outline-none cursor-pointer"
                                            >
                                                 <span class="font-semibold text-primary text-sm md:text-base">${info.title}</span>
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
                                                 x-transition:enter="transition ease-out duration-300"
                                                 x-transition:enter-start="opacity-0 max-h-0 overflow-hidden"
                                                  x-transition:enter-end="opacity-100 max-h-48 overflow-hidden"
                                                  x-transition:leave="transition ease-in duration-300"
                                                  x-transition:leave-start="opacity-100 max-h-48 overflow-hidden"
                                                 x-transition:leave-end="opacity-0 max-h-0 overflow-hidden"
                                                 id="faq-content-${index}"
                                                 class="accordion-transition"
                                                 @click.away="openFaq = null"
                                             >
                                                 <div class="accordion-content p-2 bg-primary/5 rounded-md mt-1">
                                                      <p class="text-primary/80 leading-relaxed text-sm md:text-base">${info.value}</p>
                                                 </div>
                                             </div>
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
            <div id="map-card-buttons" class="flex flex-col gap-2" style="position: relative; z-index: 1003; display: flex; flex-direction: column; gap: 8px;">
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
