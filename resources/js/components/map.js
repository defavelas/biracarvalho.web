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
        this.currentMapDialog = null;
        this.currentCloseButton = null;
        this.selectedLocationId = null;
        this.zoomControl = null;
        this.zoomObserver = null;
        this.focusTrapHandler = null;
        this.previouslyFocusedElement = null;

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

        this.zoomControl = L.control
            .zoom({
                position: "bottomright",
                zoomInTitle: "Aproximar mapa",
                zoomOutTitle: "Afastar mapa",
                zoomInText: "+",
                zoomOutText: "−",
            })
            .addTo(this.map);

        this.syncZoomButtonsAccessibility();
        this.observeZoomButtons();

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

        this.map.on("zoomend", () => {
            this.syncZoomButtonsAccessibility();
        });

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
        this.getLiveRegion().textContent =
            `Focalizando ${location.name}. ${location.typeLabel}. Pressione Enter para abrir detalhes.`;
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
        const { id, name, type, latitude, longitude, typeColor, typeLabel } = location;

        const locationId = String(id);

        const icon = this.createAccessibilityIcon(type, typeColor, locationId === this.selectedLocationId);

        // Descriptive accessible name so screen readers announce the location
        // instead of the generic "marker" repeated for every pin.
        const accessibleName = typeLabel ? `${name} — ${typeLabel}` : name;

        const marker = L.marker([latitude, longitude], {
            icon,
            alt: accessibleName,
            title: accessibleName,
            keyboard: true,
            riseOnHover: true,
        }).addTo(this.markerLayer);

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

        const portal = this.getMapCardPortal();
        if (!portal) {
            console.error("Map card portal not found");
            return;
        }

        const cardHtml = this.createMapCardHTML(location);
        this.previouslyFocusedElement = document.activeElement instanceof HTMLElement ? document.activeElement : null;

        portal.innerHTML = cardHtml;
        this.currentMapCard = document.getElementById("map-card-container");
        this.currentMapDialog = document.getElementById("map-card");
        this.currentCloseButton = document.getElementById("map-card-buttons");

        if (this.currentMapCard) {
            this.initializeMapCardInteractions(location);
            requestAnimationFrame(() => {
                this.positionMapCard(location);
                this.activateFocusTrap();
                this.focusMapCard();
            });
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

        if (this.currentCloseButton) {
            this.positionCloseButton(cardLeft, cardTop, cardWidth, cardHeight);
        }
    }

    escapeHtml(value) {
        return String(value ?? "")
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    }

    createMapCardHTML(location) {
        const images = location.images || [];
        const hasImages = images.length > 0;
        const infos = location.infos || [];
        const hasInfos = infos.length > 0;
        const isMobile = window.innerWidth < 768;
        const maxImages = Math.min(images.length, 5);
        const safeName = this.escapeHtml(location.name || "Local sem nome");
        const safeDescription = this.escapeHtml(location.description || "Sem descrição disponível");
        const safeTypeLabel = this.escapeHtml(location.typeLabel || "Tipo não informado");
        const safeAuthors = this.escapeHtml(location.authors ? `Contribuição ${location.authors}` : "Contribuição anônima");
        const locationId = String(location.id);

        let formattedDate = "";
        if (location.createdAt) {
            try {
                const date = new Date(location.createdAt);
                formattedDate = this.escapeHtml(
                    isNaN(date.getTime()) ? location.createdAt : date.toLocaleDateString("pt-BR"),
                );
            } catch {
                formattedDate = this.escapeHtml(location.createdAt);
            }
        }

        const imagesHtml = hasImages
            ? `
                <div class="relative" data-map-slideshow data-total-slides="${maxImages}">
                    <div class="relative h-56 md:h-64 overflow-hidden rounded-lg bg-black/25">
                        ${images
                            .slice(0, maxImages)
                            .map(
                                (image, index) => `
                                <div
                                    class="absolute inset-0 transition-opacity duration-150 ${index === 0 ? "" : "hidden"}"
                                    data-map-slide="${index}"
                                    aria-hidden="${index === 0 ? "false" : "true"}"
                                >
                                    <img
                                        src="${image.url}"
                                        alt="${this.escapeHtml(image.alt || `${safeName} - Imagem ${index + 1} de ${maxImages}`)}"
                                        class="h-full w-full object-cover"
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
                                data-map-slide-prev
                                class="absolute left-2 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/60 text-white transition-colors duration-200 hover:bg-black/75 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-secondary focus-visible:ring-offset-2 focus-visible:ring-offset-black/40"
                                aria-label="Imagem anterior"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <button
                                type="button"
                                data-map-slide-next
                                class="absolute right-2 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/60 text-white transition-colors duration-200 hover:bg-black/75 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-secondary focus-visible:ring-offset-2 focus-visible:ring-offset-black/40"
                                aria-label="Próxima imagem"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                            <div class="absolute bottom-2 left-1/2 flex -translate-x-1/2 items-center gap-2">
                                ${Array.from(
                                    { length: maxImages },
                                    (_, index) => `
                                        <button
                                            type="button"
                                            data-map-slide-dot="${index}"
                                            class="h-3 w-3 rounded-full transition-colors duration-200 ${index === 0 ? "bg-primary" : "bg-white/60 hover:bg-white"}"
                                            aria-label="Ir para imagem ${index + 1} de ${maxImages}"
                                            aria-pressed="${index === 0 ? "true" : "false"}"
                                        ></button>
                                    `,
                                ).join("")}
                            </div>
                        `
                                : ""
                        }
                    </div>
                </div>
            `
            : "";

        const accordionHtml = hasInfos
            ? infos
                  .map((info, index) => {
                      const buttonId = `faq-button-${locationId}-${index}`;
                      const panelId = `faq-panel-${locationId}-${index}`;

                      return `
                        <div class="border-b border-primary/10 last:border-b-0">
                            <button
                                type="button"
                                id="${buttonId}"
                                data-accordion-button
                                aria-expanded="false"
                                aria-controls="${panelId}"
                                class="map-card-accordion-button flex w-full items-center justify-between gap-4 rounded-lg py-2 text-left transition-colors duration-200 hover:bg-primary/5 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-secondary focus-visible:ring-offset-2"
                            >
                                <span class="font-semibold text-primary text-sm md:text-base">${this.escapeHtml(info.title)}</span>
                                <svg
                                    data-accordion-icon
                                    class="h-4 w-4 shrink-0 text-primary/70 transition-transform duration-200"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div
                                id="${panelId}"
                                data-accordion-panel
                                class="accordion-transition hidden"
                                aria-hidden="true"
                                role="region"
                                aria-labelledby="${buttonId}"
                            >
                                <div class="accordion-content mt-1 rounded-md bg-primary/5 p-2">
                                    <p class="text-sm leading-relaxed text-primary/80 md:text-base">${this.escapeHtml(info.value)}</p>
                                </div>
                            </div>
                        </div>
                    `;
                  })
                  .join("")
            : "";

        const desktopActions = !isMobile
            ? `
                <div id="map-card-buttons" class="flex flex-col gap-2">
                    <button
                        type="button"
                        data-map-card-action="close"
                        class="flex h-12 w-12 items-center justify-center rounded-full bg-secondary text-primary shadow-lg transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:shadow-xl focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-primary focus-visible:ring-offset-2"
                        aria-label="Fechar detalhes do local"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <button
                        type="button"
                        data-map-card-action="center"
                        data-location-lat="${location.latitude}"
                        data-location-lng="${location.longitude}"
                        class="flex h-12 w-12 items-center justify-center rounded-full bg-secondary text-primary shadow-lg transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:shadow-xl focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-primary focus-visible:ring-offset-2"
                        aria-label="Centralizar no mapa"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </button>
                </div>
            `
            : "";

        return `
            <div id="map-card-container" class="pointer-events-auto">
                ${desktopActions}
                <div
                    id="map-card"
                    class="relative flex max-h-[80vh] min-h-[300px] w-[460px] max-w-[460px] flex-col overflow-y-auto overflow-x-hidden rounded-lg border-4 border-secondary bg-white pb-2 shadow-xl"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="map-card-title"
                    aria-describedby="map-card-description"
                    tabindex="-1"
                >
                    <button
                        type="button"
                        data-map-card-action="close"
                        class="button-close absolute right-0 top-0 z-50 flex h-12 w-12 items-center justify-center rounded-full border-4 border-white bg-secondary shadow-lg transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:shadow-xl focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-primary focus-visible:ring-offset-2 md:hidden"
                        aria-label="Fechar detalhes do local"
                    >
                        <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>

                    ${
                        hasImages
                            ? `
                        <div class="relative p-2 pb-0 mobile-compact-spacing">
                            <span class="absolute left-4 top-4 z-50 inline-block rounded-full px-4 py-0.5 text-left text-white shadow" style="background-color: ${location.typeColor}">
                                ${safeTypeLabel}
                            </span>
                            ${imagesHtml}
                        </div>
                    `
                            : ""
                    }

                    <div class="flex-shrink-0 px-2 pt-2.5">
                        ${
                            !hasImages
                                ? `
                            <span class="mb-3 inline-block rounded-full px-3 py-1 text-white shadow" style="background-color: ${location.typeColor}">
                                ${safeTypeLabel}
                            </span>
                        `
                                : ""
                        }

                        <h3 id="map-card-title" class="mb-1 text-lg font-semibold leading-tight text-primary">
                            ${safeName}
                        </h3>
                        <p id="map-card-description" class="mb-2 text-base text-primary/80">
                            ${safeDescription}
                        </p>
                        <div class="mb-4 flex items-center justify-between text-sm leading-tight text-primary/80">
                            <span class="text-left">${safeAuthors}</span>
                            <span class="text-right">${formattedDate}</span>
                        </div>

                        ${
                            hasInfos
                                ? `<strong class="block text-base font-semibold text-primary md:text-lg">Informações do local</strong>`
                                : ""
                        }
                    </div>

                    <div class="flex-1 space-y-3 px-2">
                        ${
                            hasInfos
                                ? `
                            <div class="relative max-h-48 overflow-y-auto soft-scrollbar" style="max-height: 12rem;">
                                ${accordionHtml}
                            </div>
                        `
                                : ""
                        }
                    </div>
                </div>
            </div>
        `;
    }

    getMapCardPortal() {
        return document.getElementById("map-card-portal");
    }

    initializeMapCardInteractions(location) {
        if (!this.currentMapCard) {
            return;
        }

        this.currentMapCard.querySelectorAll("[data-map-card-action]").forEach((button) => {
            button.addEventListener("click", () => {
                const action = button.getAttribute("data-map-card-action");

                if (action === "close") {
                    this.closeMapCard();
                    return;
                }

                if (action === "center") {
                    this.closeMapCard({ restoreFocus: false, clearSelection: false });
                    this.setView(Number(location.latitude), Number(location.longitude), 18);
                }
            });
        });

        this.initializeSlideshow();
        this.initializeAccordions(location);
    }

    initializeSlideshow() {
        if (!this.currentMapCard) {
            return;
        }

        const slideshow = this.currentMapCard.querySelector("[data-map-slideshow]");
        if (!slideshow) {
            return;
        }

        const slides = Array.from(slideshow.querySelectorAll("[data-map-slide]"));
        const dots = Array.from(slideshow.querySelectorAll("[data-map-slide-dot]"));
        const prevButton = slideshow.querySelector("[data-map-slide-prev]");
        const nextButton = slideshow.querySelector("[data-map-slide-next]");

        if (slides.length <= 1) {
            return;
        }

        const setSlide = (index) => {
            slides.forEach((slide) => {
                const slideIndex = Number(slide.getAttribute("data-map-slide"));
                const isActive = slideIndex === index;

                slide.classList.toggle("hidden", !isActive);
                slide.setAttribute("aria-hidden", isActive ? "false" : "true");
            });

            dots.forEach((dot) => {
                const dotIndex = Number(dot.getAttribute("data-map-slide-dot"));
                const isActive = dotIndex === index;

                dot.setAttribute("aria-pressed", isActive ? "true" : "false");
                dot.classList.toggle("bg-primary", isActive);
                dot.classList.toggle("bg-white/60", !isActive);
            });

            slideshow.setAttribute("data-current-slide", String(index));
        };

        const getCurrentSlide = () => Number(slideshow.getAttribute("data-current-slide") || "0");
        setSlide(0);

        prevButton?.addEventListener("click", () => {
            const currentIndex = getCurrentSlide();
            setSlide(currentIndex === 0 ? slides.length - 1 : currentIndex - 1);
        });

        nextButton?.addEventListener("click", () => {
            const currentIndex = getCurrentSlide();
            setSlide(currentIndex === slides.length - 1 ? 0 : currentIndex + 1);
        });

        dots.forEach((dot) => {
            dot.addEventListener("click", () => {
                setSlide(Number(dot.getAttribute("data-map-slide-dot")));
            });
        });
    }

    initializeAccordions(location) {
        if (!this.currentMapCard) {
            return;
        }

        const buttons = Array.from(this.currentMapCard.querySelectorAll("[data-accordion-button]"));

        buttons.forEach((button) => {
            button.addEventListener("click", () => {
                const willExpand = button.getAttribute("aria-expanded") !== "true";
                const panelId = button.getAttribute("aria-controls");

                buttons.forEach((otherButton) => {
                    const otherPanel = document.getElementById(otherButton.getAttribute("aria-controls"));
                    const shouldExpand = otherButton === button ? willExpand : false;

                    this.setAccordionState(otherButton, otherPanel, shouldExpand);
                });

                this.repositionCurrentMapCard(location);

                if (willExpand) {
                    const panel = document.getElementById(panelId);
                    this.scrollAccordionIntoView(panel);
                }
            });
        });
    }

    setAccordionState(button, panel, expanded) {
        if (!button || !panel) {
            return;
        }

        button.setAttribute("aria-expanded", expanded ? "true" : "false");
        button.classList.toggle("bg-primary/5", expanded);

        const icon = button.querySelector("[data-accordion-icon]");
        if (icon) {
            icon.classList.toggle("rotate-180", expanded);
        }

        panel.hidden = !expanded;
        panel.classList.toggle("hidden", !expanded);
        panel.setAttribute("aria-hidden", expanded ? "false" : "true");
    }

    scrollAccordionIntoView(panel) {
        if (!panel || !this.currentMapDialog) {
            return;
        }

        requestAnimationFrame(() => {
            panel.scrollIntoView({
                behavior: "smooth",
                block: "nearest",
            });

            this.currentMapDialog.scrollTop = Math.max(panel.offsetTop - 12, 0);
        });
    }

    repositionCurrentMapCard(location = null) {
        const currentLocation = location ?? this.getLocationById(this.selectedLocationId);
        if (!currentLocation || !this.currentMapCard) {
            return;
        }

        requestAnimationFrame(() => {
            this.positionMapCard(currentLocation);
        });
    }

    activateFocusTrap() {
        if (!this.currentMapCard) {
            return;
        }

        this.deactivateFocusTrap();

        this.focusTrapHandler = (event) => {
            if (event.key === "Escape") {
                event.preventDefault();
                this.closeMapCard();
                return;
            }

            if (event.key !== "Tab") {
                return;
            }

            const focusableElements = this.getFocusableMapCardElements();
            if (focusableElements.length === 0) {
                event.preventDefault();
                this.currentMapDialog?.focus();
                return;
            }

            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            if (event.shiftKey && document.activeElement === firstElement) {
                event.preventDefault();
                lastElement.focus();
                return;
            }

            if (!event.shiftKey && document.activeElement === lastElement) {
                event.preventDefault();
                firstElement.focus();
            }
        };

        this.currentMapCard.addEventListener("keydown", this.focusTrapHandler);
    }

    deactivateFocusTrap() {
        if (this.currentMapCard && this.focusTrapHandler) {
            this.currentMapCard.removeEventListener("keydown", this.focusTrapHandler);
        }

        this.focusTrapHandler = null;
    }

    getFocusableMapCardElements() {
        if (!this.currentMapCard) {
            return [];
        }

        return Array.from(
            this.currentMapCard.querySelectorAll(
                'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
            ),
        ).filter((element) => !element.hidden && element.getClientRects().length > 0);
    }

    focusMapCard() {
        const focusableElements = this.getFocusableMapCardElements();
        const preferredElement = focusableElements[0] ?? this.currentMapDialog;

        preferredElement?.focus({ preventScroll: true });
    }

    restorePreviousFocus() {
        if (
            this.previouslyFocusedElement &&
            document.contains(this.previouslyFocusedElement) &&
            typeof this.previouslyFocusedElement.focus === "function"
        ) {
            this.previouslyFocusedElement.focus({ preventScroll: true });
        }

        this.previouslyFocusedElement = null;
    }

    closeMapCard({ restoreFocus = true, clearSelection = true } = {}) {
        this.deactivateFocusTrap();

        if (this.currentMapCard) {
            this.currentMapCard.remove();
            this.currentMapCard = null;
        }

        this.currentMapDialog = null;
        this.currentCloseButton = null;

        const portal = this.getMapCardPortal();
        if (portal) {
            portal.innerHTML = "";
        }

        if (clearSelection) {
            this.selectedLocationId = null;
            this.updateMarkerStyles();
        }

        if (restoreFocus) {
            this.restorePreviousFocus();
        } else {
            this.previouslyFocusedElement = null;
        }
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

    getLiveRegion() {
        let liveRegion = document.getElementById("map-live-region");
        if (!liveRegion) {
            liveRegion = document.createElement("div");
            liveRegion.id = "map-live-region";
            liveRegion.setAttribute("aria-live", "polite");
            liveRegion.setAttribute("aria-atomic", "true");
            liveRegion.className = "sr-only";
            document.body.appendChild(liveRegion);
        }

        return liveRegion;
    }

    clearLiveRegionAnnouncement() {
        this.getLiveRegion().textContent = "";
    }

    observeZoomButtons() {
        if (!this.map || this.zoomObserver) {
            return;
        }

        const zoomControl = this.map.getContainer().querySelector(".leaflet-control-zoom");
        if (!zoomControl) {
            return;
        }

        this.zoomObserver = new MutationObserver(() => {
            this.syncZoomButtonsAccessibility();
        });

        this.zoomObserver.observe(zoomControl, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ["class"],
        });
    }

    syncZoomButtonsAccessibility() {
        const zoomControl = this.map?.getContainer().querySelector(".leaflet-control-zoom");
        if (!zoomControl) {
            return;
        }

        const zoomIn = zoomControl.querySelector(".leaflet-control-zoom-in");
        const zoomOut = zoomControl.querySelector(".leaflet-control-zoom-out");

        this.updateZoomButtonAccessibility(zoomIn, "Aproximar mapa");
        this.updateZoomButtonAccessibility(zoomOut, "Afastar mapa");
    }

    updateZoomButtonAccessibility(button, label) {
        if (!button) {
            return;
        }

        const isDisabled = button.classList.contains("leaflet-disabled");

        button.setAttribute("title", label);
        button.setAttribute("aria-label", label);
        button.setAttribute("role", "button");
        button.setAttribute("lang", "pt-BR");

        if (isDisabled) {
            button.setAttribute("aria-disabled", "true");
            button.setAttribute("tabindex", "-1");
        } else {
            button.removeAttribute("aria-disabled");
            button.setAttribute("tabindex", "0");
        }

        if (button.dataset.accessibilityBound !== "true") {
            button.addEventListener("focus", () => {
                this.clearLiveRegionAnnouncement();
            });

            button.dataset.accessibilityBound = "true";
        }
    }

    destroy() {
        this.closeMapCard();
        if (this.zoomObserver) {
            this.zoomObserver.disconnect();
            this.zoomObserver = null;
        }
        if (this.map) {
            this.map.remove();
        }
    }
}

window.closeMapCard = function (options = {}) {
    const mapComponent = window.mapComponentInstance;
    if (mapComponent) {
        mapComponent.closeMapCard(options);
    }
};

window.centerMapOnLocation = function (lat, lng) {
    const mapComponent = window.mapComponentInstance;
    if (mapComponent) {
        mapComponent.closeMapCard({ restoreFocus: false, clearSelection: false });
        mapComponent.setView(lat, lng, 18);
    }
};

window.highlightLocationInSidebar = function (locationId) {
    if (typeof Livewire !== "undefined") {
        Livewire.dispatch("open-sidebar");
        Livewire.dispatch("reveal-location-in-sidebar", {
            locationId: String(locationId),
        });
    }
};

window.MapComponent = MapComponent;

window.initializeMap = function (containerId, options = {}) {
    const instance = new MapComponent(containerId, options);

    window.mapComponentInstance = instance;

    return instance;
};
