<div class="relative w-full h-screen overflow-hidden">
    <!-- Skip Link for Accessibility -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-white px-4 py-2 rounded-md shadow-lg z-50 text-purple-600">
        Pular para o conteúdo principal
    </a>

    <!-- Map Container -->
    <main id="main-content" class="w-full h-screen relative z-[1]" role="main" aria-label="Mapa interativo de acessibilidade">
        <x-osm-map 
            id="accessibility-map" 
            class="w-full h-screen"
        />
    </main>

    <!-- Search Sidebar -->
    <livewire:search-sidebar :collapsed="$sidebarCollapsed" />

    <!-- Floating Buttons -->
    <div class="floating-controls">
        <!-- Toggle Sidebar Button -->
        <x-floating-btn 
            position="top-left"
            aria-label="Alternar painel de pesquisa"
            wire:click="toggleSidebar"
        >
            <span class="text-lg">{{ $sidebarCollapsed ? '☰' : '✕' }}</span>
        </x-floating-btn>

        <!-- Settings Button (Future Authentication) -->
        <x-floating-btn 
            position="top-right"
            aria-label="Configurações (em breve)"
            disabled
        >
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </x-floating-btn>
    </div>

        <!-- Screen Reader Status Updates -->
    <div aria-live="polite" aria-atomic="true" class="sr-only" id="status-updates">
        @if($sidebarCollapsed)
            Painel de pesquisa fechado
        @else
            Painel de pesquisa aberto
        @endif
    </div>
</div>