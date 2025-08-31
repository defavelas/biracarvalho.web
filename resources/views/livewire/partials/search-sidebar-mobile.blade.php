<!-- Mobile: Floating search bar -->
<div class="md:hidden">
    <!-- Mobile floating search container -->
    <div class="fixed top-2 left-2 right-2 z-[1001] transform transition-all duration-300 ease-in-out translate-y-0 opacity-100"
         aria-label="Barra de pesquisa móvel"
         aria-hidden="false">

        <!-- Compact search bar -->
        <div class="bg-primary rounded-lg shadow-xl border-4 border-black/15 overflow-hidden">
            <div class="bg-no-repeat bg-top" style="background-image: url('{{ asset('assets/images/search-bg.jpg') }}');">
                <!-- Logo and search in one row -->
                <div class="flex items-center gap-4 mb-2 p-2 pb-0">
                    <img src="{{ asset('assets/images/logo.svg') }}" alt="Logo" class="w-16 h-auto flex-shrink-0">
                    <div class="flex-1 relative">
                        <label for="mobile-search-input" class="sr-only">Pesquisar locais</label>
                        <input type="search" id="mobile-search-input" wire:model.live.debounce.300ms="search"
                            placeholder="Pesquisar locais..."
                            class="bg-white w-full px-3 py-2 pr-8 text-base border-2 border-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-black/25 transition-all duration-200"
                            aria-describedby="mobile-search-help">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                            @svg('heroicon-o-magnifying-glass', 'w-6 h-6 text-primary')
                        </div>
                    </div>
                </div>

                <div class="flex flex-col p-2 bg-black/20" wire:key="filter-toggles">
                    <legend class="text-sm font-semibold text-white pb-1">
                        Filtrar por
                    </legend>
                    <div class="flex items-center gap-x-4">
                        <x-toggle-button wire:model.live="typeFilters.accessible" :value="$typeFilters['accessible']"
                            label="Acessível" trackClass="bg-black/20 border-white" thumbClass="bg-green-500"
                            labelClass="text-white text-sm" wire:key="filter-accessible" />

                        <x-toggle-button wire:model.live="typeFilters.non_accessible" :value="$typeFilters['non_accessible']"
                            label="Não Acessível" trackClass="bg-black/20 border-white" thumbClass="bg-amber-500"
                            labelClass="text-white text-sm" wire:key="filter-non-accessible" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile search results cards (50% screen height) -->
    @if((!empty($search) || array_sum($typeFilters) > 0) && $resultsOpen)
    <div class="fixed bottom-0 left-0 right-0 h-[50vh] bg-primary/95 backdrop-blur-sm z-[999] transform transition-all duration-300 ease-in-out translate-y-0"
         style="background-image: linear-gradient(to bottom, rgba(101, 48, 137, 0.95), rgba(101, 48, 137, 0.98));">

        <!-- Results header -->
        <div class="p-2.5 bg-black/20">
            <div class="flex items-center justify-between">
                <div>
                <h3 class="text-sm font-semibold text-white">
                    Resultados da Pesquisa
                </h3>
                <span class="text-xs font-mono text-white/80">
                    {{ $totalResults }} {{ $totalResults === 1 ? 'local' : 'locais' }}
                </span>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="closeResults"
                        class="flex items-center justify-center w-8 h-8 bg-black/30 hover:bg-black/50 rounded-full text-white/70 hover:text-white transition-all duration-200"
                        aria-label="Fechar resultados para melhor navegação no mapa">
                        @svg('heroicon-o-x-mark', 'w-4 h-4')
                    </button>
                    @if (array_sum($typeFilters) > 0 || !empty($search))
                        <button wire:click="clearFilters"
                            class="flex items-center justify-center w-8 h-8 bg-black/30 hover:bg-black/50 rounded-full text-white/70 hover:text-white transition-all duration-200"
                            aria-label="Limpar todos os filtros">
                            @svg('heroicon-o-trash', 'w-4 h-4')
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Scrollable results container -->
        <div class="flex-1 overflow-y-auto soft-scrollbar" style="height: calc(50vh - 70px);">
            <div class="p-2 space-y-2">
                @forelse($results as $result)
                    <article
                        class="bg-black/25 border border-black/20 rounded-lg p-3 hover:bg-black/35 hover:border-secondary cursor-pointer transition-all duration-200 group"
                        data-location-id="{{ $result['id'] }}"
                        role="button"
                        tabindex="0"
                        aria-label="Ver {{ $result['name'] }} no mapa - {{ $result['typeLabel'] }}"
                        wire:click="focusLocation('{{ $result['id'] }}')"
                        wire:keydown.enter="focusLocation('{{ $result['id'] }}')"
                        wire:keydown.space="focusLocation('{{ $result['id'] }}')"
                        wire:loading.class="opacity-75 pointer-events-none">

                        <div class="flex items-start justify-between">
                            <div class="flex-1 space-y-2">
                                <div class="flex items-start gap-2">
                                    <div class="w-3 h-3 rounded-full mt-1 flex-shrink-0 bg-{{ $result['typeColor'] }}-500"
                                        aria-hidden="true">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-secondary text-sm leading-tight mb-1 truncate">
                                            {{ $result['name'] }}
                                        </h4>
                                        <p class="text-xs text-white/70 leading-relaxed line-clamp-2">
                                            {{ $result['type'] ?? 'Local de acessibilidade' }}
                                        </p>
                                        @if($result['authors'])
                                            <p class="text-xs text-white/60 leading-relaxed">
                                                Por: {{ $result['authors'] }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 leading-4 font-medium rounded-full text-xs text-white bg-{{ $result['typeColor'] }}-500">
                                        {{ $result['typeLabel'] }}
                                    </span>

                                    @if (isset($result['images']) && count($result['images']) > 0)
                                        <div class="flex -space-x-1">
                                            @foreach (array_slice($result['images'], 0, 2) as $index => $image)
                                                <div class="w-6 h-6 rounded-full overflow-hidden bg-black/25 border border-white/30">
                                                    <img src="{{ $image['url'] }}"
                                                        alt="Imagem {{ $index + 1 }} de {{ $result['name'] }}"
                                                        class="w-full h-full object-cover" loading="lazy">
                                                </div>
                                            @endforeach
                                            @if (count($result['images']) > 2)
                                                <div class="w-6 h-6 rounded-full bg-black/40 border border-white/30 flex items-center justify-center">
                                                    <span class="text-xs text-white/70 font-medium">+{{ count($result['images']) - 2 }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex-shrink-0 self-center ml-3 group-hover:translate-x-1 transition-transform duration-200">
                                @svg('heroicon-o-arrow-right', 'w-4 h-4 text-white/50 group-hover:text-secondary', ['aria-hidden' => 'true'])
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="py-12 text-center">
                        <div class="mx-auto w-12 h-12 bg-black/25 rounded-full flex items-center justify-center mb-3">
                            @svg('heroicon-o-magnifying-glass', 'w-6 h-6 text-secondary')
                        </div>
                        <h4 class="text-sm font-semibold text-secondary mb-1">Nenhum resultado encontrado</h4>
                        <p class="text-xs text-white/50">Tente ajustar seus filtros de acessibilidade ou termo de pesquisa</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    <!-- Results indicator when closed -->
    @if((!empty($search) || array_sum($typeFilters) > 0) && !$resultsOpen && $totalResults > 0)
    <div class="fixed bottom-4 left-1/2 transform -translate-x-1/2 z-[998]" id="mobile-results-indicator">
        <button wire:click="openResults"
            class="flex items-center gap-2 bg-secondary text-primary px-4 py-2 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 text-sm font-medium"
            aria-label="Mostrar {{ $totalResults }} {{ $totalResults === 1 ? 'resultado' : 'resultados' }}">
            @svg('heroicon-o-chevron-up', 'w-4 h-4')
            <span>{{ $totalResults }} {{ $totalResults === 1 ? 'resultado' : 'resultados' }}</span>
        </button>
    </div>
    @endif
</div>
