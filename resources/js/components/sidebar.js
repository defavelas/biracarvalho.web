/**
 * Sidebar Component for Maré Accessibility Mapping
 * Handles sidebar toggle and UI interactions
 */

class SidebarComponent {
    constructor() {
        this.sidebar = null;
        this.toggleButton = null;
        this.isCollapsed = false;
        
        this.init();
    }
    
    init() {
        // Find sidebar and toggle button
        this.sidebar = document.querySelector('.floating-sidebar');
        this.toggleButton = document.querySelector('.floating-btn-toggle');
        
        if (!this.sidebar) {
            console.warn('Sidebar not found');
            return;
        }
        
        // Setup event listeners
        this.setupEventListeners();
        
        console.log('Sidebar component initialized');
    }
    
    setupEventListeners() {
        // Toggle button click
        if (this.toggleButton) {
            this.toggleButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggle();
            });
        }
        
        // Keyboard accessibility - ESC to close sidebar
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.isCollapsed) {
                this.collapse();
            }
        });
        
        // Listen for Livewire events to sync state
        document.addEventListener('livewire:init', () => {
            Livewire.on('sidebar-toggled', (event) => {
                this.isCollapsed = event.collapsed;
                this.updateUI();
            });
        });
        
        // Handle marker clicks to highlight results
        document.addEventListener('marker-clicked', (e) => {
            this.highlightResult(e.detail.id);
        });
    }
    
    toggle() {
        if (this.isCollapsed) {
            this.expand();
        } else {
            this.collapse();
        }
        
        // Dispatch Livewire event to sync state
        if (window.Livewire) {
            Livewire.dispatch('toggle-sidebar');
        }
    }
    
    collapse() {
        this.isCollapsed = true;
        this.updateUI();
        this.updateToggleButtonIcon();
        
        // Update aria attributes for accessibility
        this.sidebar.setAttribute('aria-hidden', 'true');
        
        // Focus management - move focus to toggle button
        if (this.toggleButton) {
            this.toggleButton.focus();
        }
    }
    
    expand() {
        this.isCollapsed = false;
        this.updateUI();
        this.updateToggleButtonIcon();
        
        // Update aria attributes for accessibility
        this.sidebar.setAttribute('aria-hidden', 'false');
        
        // Focus management - move focus to search input
        setTimeout(() => {
            const searchInput = this.sidebar.querySelector('#search-input');
            if (searchInput) {
                searchInput.focus();
            }
        }, 300); // Wait for animation to complete
    }
    
    updateUI() {
        if (!this.sidebar) return;
        
        if (this.isCollapsed) {
            this.sidebar.classList.add('collapsed');
        } else {
            this.sidebar.classList.remove('collapsed');
        }
    }
    
    updateToggleButtonIcon() {
        if (!this.toggleButton) return;
        
        const icon = this.isCollapsed ? '☰' : '✕';
        const ariaLabel = this.isCollapsed ? 'Abrir painel de pesquisa' : 'Fechar painel de pesquisa';
        
        this.toggleButton.innerHTML = `<span style="font-size: 18px;">${icon}</span>`;
        this.toggleButton.setAttribute('aria-label', ariaLabel);
    }
    
    highlightResult(locationId) {
        // Remove existing highlights
        const existingHighlights = this.sidebar.querySelectorAll('.result-highlighted');
        existingHighlights.forEach(el => el.classList.remove('result-highlighted'));
        
        // Add highlight to clicked result
        const resultElement = this.sidebar.querySelector(`[data-location-id="${locationId}"]`);
        if (resultElement) {
            resultElement.classList.add('result-highlighted');
            
            // Scroll to the highlighted result
            resultElement.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    }
    
    // Public methods
    getState() {
        return {
            isCollapsed: this.isCollapsed
        };
    }
}

// Initialize sidebar component when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.sidebarComponent = new SidebarComponent();
});

// Export for use in other modules
window.SidebarComponent = SidebarComponent; 