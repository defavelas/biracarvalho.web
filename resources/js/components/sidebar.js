/**
 * Sidebar accessibility helpers.
 * Syncs Livewire state changes with focus and result reveal behavior.
 */
class SidebarComponent {
    constructor() {
        this.sidebar = null;
        this.toggleButton = null;
        this.isCollapsed = false;
        this.pendingLocationId = null;

        this.init();
    }

    init() {
        this.syncElements();
        this.setupEventListeners();
    }

    syncElements() {
        this.sidebar = document.getElementById("desktop-search-sidebar");
        this.toggleButton = document.querySelector('button[aria-controls="desktop-search-sidebar"]');
    }

    setupEventListeners() {
        document.addEventListener("livewire:init", () => {
            Livewire.on("sidebar-toggled", (event) => {
                this.syncElements();
                this.isCollapsed = Boolean(event.collapsed);
                this.syncSidebarAttributes();

                if (!this.isCollapsed && this.pendingLocationId) {
                    requestAnimationFrame(() => {
                        this.focusSidebarResult(this.pendingLocationId);
                    });
                    return;
                }

                if (!this.isCollapsed) {
                    requestAnimationFrame(() => {
                        this.focusSearchInput();
                    });
                }
            });

            Livewire.on("sidebar-location-revealed", (event) => {
                this.focusSidebarResult(event.locationId);
            });
        });
    }

    syncSidebarAttributes() {
        if (!this.sidebar || !this.toggleButton) {
            return;
        }

        this.sidebar.setAttribute("aria-hidden", this.isCollapsed ? "true" : "false");
        this.toggleButton.setAttribute("aria-expanded", this.isCollapsed ? "false" : "true");
    }

    getVisibleResult(locationId) {
        const selector = `[data-sidebar-result="true"][data-location-id="${locationId}"]`;
        const results = Array.from(document.querySelectorAll(selector));

        return (
            results.find((result) => {
                const element = result;

                return !element.hasAttribute("hidden") && element.offsetParent !== null;
            }) ?? null
        );
    }

    focusSidebarResult(locationId) {
        const resultElement = this.getVisibleResult(locationId);

        if (!resultElement) {
            this.pendingLocationId = locationId;
            return;
        }

        this.pendingLocationId = null;

        document.querySelectorAll(".result-highlighted").forEach((element) => {
            element.classList.remove("result-highlighted");
        });

        resultElement.classList.add("result-highlighted");
        resultElement.scrollIntoView({
            behavior: "smooth",
            block: "center",
        });

        requestAnimationFrame(() => {
            resultElement.focus({ preventScroll: true });
        });
    }

    focusSearchInput() {
        const searchInput =
            document.getElementById("search-input")?.offsetParent !== null
                ? document.getElementById("search-input")
                : document.getElementById("mobile-search-input");

        searchInput?.focus({ preventScroll: true });
    }
}

document.addEventListener("DOMContentLoaded", () => {
    window.sidebarComponent = new SidebarComponent();
});

window.SidebarComponent = SidebarComponent;
