import "./bootstrap";
import anchor from '@alpinejs/anchor';

// Import modular components
import "./components/map";
import "./components/sidebar";

// Register Alpine Anchor plugin with Livewire's Alpine instance
document.addEventListener('livewire:init', () => {
    if (window.Alpine) {
        window.Alpine.plugin(anchor);
    }
});

// Initialize components when DOM is ready
document.addEventListener("DOMContentLoaded", function () {});
