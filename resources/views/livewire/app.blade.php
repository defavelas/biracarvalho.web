<div class="relative w-full h-screen overflow-hidden">
    <a href="#search-input" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-white px-4 py-2 rounded-md shadow-lg z-[9999] text-primary font-semibold focus:ring-2 focus:ring-secondary focus:ring-offset-2">
        Pular para busca
    </a>
    <a href="#accessibility-map" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-40 bg-white px-4 py-2 rounded-md shadow-lg z-[9999] text-primary font-semibold focus:ring-2 focus:ring-secondary focus:ring-offset-2">
        Pular para o mapa
    </a>

    <main id="main-content" class="fixed inset-0 w-full h-screen z-0" role="main" aria-label="Mapa interativo de acessibilidade" wire:ignore>
        <x-osm-map 
            id="accessibility-map" 
            class="w-full h-full"
        />
    </main>

    <div id="map-card-portal" class="absolute inset-0 z-[150] pointer-events-none" aria-live="polite"></div>

    <livewire:search-sidebar :collapsed="$sidebarCollapsed" />

    <button
        type="button"
        class="hidden md:flex absolute z-[1002] w-12 h-12 rounded-full bg-secondary shadow-lg border-0 cursor-pointer items-center justify-center transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-0.5
               top-5 {{ $sidebarCollapsed ? 'left-5' : 'left-[25.5rem]' }}"
        wire:click="toggleSidebar"
        aria-controls="desktop-search-sidebar"
        aria-expanded="{{ $sidebarCollapsed ? 'false' : 'true' }}"
        aria-label="{{ $sidebarCollapsed ? 'Abrir painel de pesquisa' : 'Fechar painel de pesquisa' }}"
    >
        @if($sidebarCollapsed)
            @svg('heroicon-o-bars-3', 'w-6 h-6 text-primary transition-transform duration-200')
        @else
            @svg('heroicon-o-x-mark', 'w-6 h-6 text-primary transition-transform duration-200')
        @endif
    </button>

    <button
        type="button"
        class="flex absolute z-[1002] w-12 h-12 rounded-full bg-secondary shadow-lg border-0 cursor-pointer items-center justify-center transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-0.5 top-5 right-5"
        wire:click="openAccessibilityModal"
        aria-label="Relatar problema de acessibilidade"
    >
        @svg('heroicon-o-flag', 'w-6 h-6 text-primary transition-transform duration-200')
    </button>

    <div
        x-data="{ show: @entangle('showAccessibilityModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="accessibility-modal-title"
        @keydown.escape.window="$wire.closeAccessibilityModal()"
    >
        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 backdrop-blur-sm"
             @click="$wire.closeAccessibilityModal()">
        </div>

        <div x-show="show"
             x-transition:enter="ease-out duration-400 delay-150"
             x-transition:enter-start="opacity-0 translate-y-8"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-8"
             class="relative w-full max-w-lg mx-4 bg-white border-4 border-secondary rounded-xl shadow-xl overflow-hidden"
             x-trap.noscroll="show">

            <div class="bg-primary p-4">
                <div class="flex items-center justify-between">
                    <h2 id="accessibility-modal-title" class="text-lg font-bold text-white">
                        Relatar Problema de Acessibilidade
                    </h2>
                    <button
                        type="button"
                        @click="$wire.closeAccessibilityModal()"
                        class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition-colors focus:outline-none focus:ring-2 focus:ring-white/50"
                        aria-label="Fechar modal"
                    >
                        @svg('heroicon-o-x-mark', 'w-5 h-5 text-white')
                    </button>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <p class="text-primary/80 leading-relaxed">
                    Encontrou alguma barreira de acessibilidade neste site? Sua opinião é muito importante para melhorarmos a experiência de todos os usuários.
                </p>

                <div class="bg-primary/5 rounded-lg p-4">
                    <p class="flex items-center gap-2 text-sm text-primary/70 mb-2">
                        @svg('heroicon-o-envelope', 'w-4 h-4')
                        <span>Entre em contato conosco:</span>
                    </p>
                    <a href="mailto:{{ config('services.accessibility.support_email') }}"
                       class="text-sm text-primary font-semibold hover:underline focus:outline-none focus:ring-2 focus:ring-secondary focus:ring-offset-2 rounded break-all">
                        {{ config('services.accessibility.support_email') }}
                    </a>
                </div>

                <p class="text-xs text-primary/60">
                    Descreva o problema encontrado, a página onde ocorreu e, se possível, qual tecnologia assistiva você utiliza.
                </p>
            </div>

            <div class="bg-primary/5 px-6 py-4 flex justify-end">
                <button
                    type="button"
                    @click="$wire.closeAccessibilityModal()"
                    class="bg-secondary text-primary font-semibold px-6 py-2 rounded-lg hover:bg-secondary/90 transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    Fechar
                </button>
            </div>
        </div>
    </div>

    

    <div aria-live="polite" aria-atomic="true" class="sr-only" id="status-updates">
        @if($sidebarCollapsed)
            Painel de pesquisa fechado
        @else
            Painel de pesquisa aberto
        @endif
    </div>
</div>
