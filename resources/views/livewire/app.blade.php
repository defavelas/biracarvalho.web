<div class="relative w-full h-screen overflow-hidden">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-white px-4 py-2 rounded-md shadow-lg z-[9999] text-primary font-semibold focus:ring-2 focus:ring-secondary focus:ring-offset-2">
        Pular para o mapa
    </a>

    <livewire:search-sidebar :collapsed="$sidebarCollapsed" />

    <main id="main-content" class="fixed inset-0 w-full h-screen z-0" role="main" aria-label="Mapa interativo de acessibilidade" tabindex="-1" wire:ignore>
        <x-osm-map 
            id="accessibility-map" 
            class="w-full h-full"
        />
    </main>

    <div id="map-card-portal" class="absolute inset-0 z-[150] pointer-events-none" aria-live="polite"></div>

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

    @if(!empty(config('services.accessibility.repository_url')))
        <a
            href="{{ config('services.accessibility.repository_url') }}"
            target="_blank"
            rel="noopener noreferrer"
            class="flex absolute z-[1002] w-12 h-12 rounded-full bg-secondary shadow-lg border-0 cursor-pointer items-center justify-center transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-0.5 top-5 right-[4.75rem] focus:outline-none focus-visible:ring-4 focus-visible:ring-primary focus-visible:ring-offset-2"
            aria-label="Ver projeto no GitHub (abre em nova aba)"
        >
            <svg class="w-6 h-6 text-primary transition-transform duration-200" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
            </svg>
        </a>
    @endif

    <button
        type="button"
        class="flex absolute z-[1002] w-12 h-12 rounded-full bg-secondary shadow-lg border-0 cursor-pointer items-center justify-center transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-0.5 top-5 right-5"
        wire:click="openAccessibilityModal"
        aria-label="Relatar problema de acessibilidade nesta ferramenta"
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
                        Relatar problema nesta ferramenta
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
                    Este canal é exclusivo para relatar problemas de acessibilidade <strong>neste site/ferramenta</strong>
                    (por exemplo, dificuldade para navegar pelo teclado, leitor de tela ou contraste).
                </p>
                <p class="text-primary/70 text-sm leading-relaxed">
                    Para relatar uma barreira de acessibilidade <strong>em um local do mapa</strong>, utilize o
                    cadastro de locais — este formulário não é destinado a esse fim.
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
                    Descreva o problema encontrado na ferramenta, a página onde ocorreu e, se possível, qual tecnologia assistiva você utiliza.
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
