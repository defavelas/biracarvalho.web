<div class="relative w-full h-screen overflow-hidden">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-white px-4 py-2 rounded-md shadow-lg z-50 text-purple-600">
        Pular para o conteúdo principal
    </a>

    <main id="main-content" class="fixed inset-0 w-full h-screen z-0" role="main" aria-label="Mapa interativo de acessibilidade" wire:ignore>
        <x-osm-map 
            id="accessibility-map" 
            class="w-full h-full"
        />
    </main>

    <livewire:search-sidebar :collapsed="$sidebarCollapsed" />

    <!-- Desktop: Sidebar toggle button -->
    <button 
        type="button"
        class="hidden md:flex absolute z-[1002] w-12 h-12 rounded-full bg-secondary shadow-lg border-0 cursor-pointer items-center justify-center transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-0.5 
               top-5 {{ $sidebarCollapsed ? 'left-5' : 'left-[25.5rem]' }}"
        wire:click="toggleSidebar"
        aria-label="{{ $sidebarCollapsed ? 'Abrir painel de pesquisa' : 'Fechar painel de pesquisa' }}"
    >
        @if($sidebarCollapsed)
            @svg('heroicon-o-bars-3', 'w-6 h-6 text-primary transition-transform duration-200')
        @else
            @svg('heroicon-o-x-mark', 'w-6 h-6 text-primary transition-transform duration-200')
        @endif
    </button>

    <livewire:auth-modal />

    <div aria-live="polite" aria-atomic="true" class="sr-only" id="status-updates">
        @if($sidebarCollapsed)
            Painel de pesquisa fechado
        @else
            Painel de pesquisa aberto
        @endif
    </div>
</div>