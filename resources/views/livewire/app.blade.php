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

    <livewire:auth-modal />

    <div aria-live="polite" aria-atomic="true" class="sr-only" id="status-updates">
        @if($sidebarCollapsed)
            Painel de pesquisa fechado
        @else
            Painel de pesquisa aberto
        @endif
    </div>
</div>