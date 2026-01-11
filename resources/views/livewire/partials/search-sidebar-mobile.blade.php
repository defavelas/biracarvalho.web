<div class="md:hidden">
    <h1 class="sr-only">Pesquisa de Locais</h1>
    <div class="fixed top-2 left-2 right-2 z-[1001] transform transition-all duration-300 ease-in-out translate-y-0 opacity-100"
         aria-label="Barra de pesquisa móvel"
         aria-hidden="false">

        <div class="bg-primary rounded-lg shadow-xl border-4 border-black/15 overflow-hidden">
            <div class="bg-no-repeat bg-top" style="background-image: url('{{ asset('assets/images/search-bg.jpg') }}');">
                <div class="flex items-center gap-4 mb-2 p-2 pb-0">
                    <img src="{{ asset('assets/images/logo.svg') }}" alt="Logo" class="w-16 h-auto flex-shrink-0">
                    <div class="flex-1 relative">
                        <label for="mobile-search-input" class="sr-only">Pesquisar locais</label>
                        <input type="search" id="mobile-search-input" wire:model.live.debounce.300ms="search"
                            placeholder="Pesquisar locais..."
                            class="bg-white w-full px-3 py-2 pr-8 text-base border-2 border-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-black/25 focus:ring-offset-1 transition-all duration-200"
                            aria-describedby="mobile-search-help"
                            autofocus
                            x-data
                            x-init="$el.focus()"
                            x-on:keydown.escape="$el.blur()">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                            @svg('heroicon-o-magnifying-glass', 'w-6 h-6 text-primary')
                        </div>
                    </div>
                </div>
                <div id="mobile-search-help" class="sr-only">
                    Digite para pesquisar por nome de locais. Pressione Escape para sair do campo de pesquisa.
                </div>

                <fieldset class="flex flex-col p-2 bg-black/20" wire:key="filter-toggles">
                    <legend class="text-sm font-semibold text-white pb-1">
                        Filtrar por
                    </legend>
                    <div class="flex items-center gap-x-4">
                        <x-toggle-button wire:model.live="typeFilters.accessible" :value="$typeFilters['accessible']"
                            label="Acessível" trackClass="bg-black/20 border-white" thumbClass="bg-green-500"
                            labelClass="text-white text-sm" />

                        <x-toggle-button wire:model.live="typeFilters.non_accessible" :value="$typeFilters['non_accessible']"
                            label="Não Acessível" trackClass="bg-black/20 border-white" thumbClass="bg-amber-500"
                            labelClass="text-white text-sm" />
                    </div>
                </fieldset>
            </div>
        </div>
    </div>

    @if((!empty($search) || array_sum($typeFilters) > 0) && $resultsOpen)
    <div class="fixed bottom-0 left-0 right-0 h-[45vh] bg-primary/95 backdrop-blur-sm z-[999] transform transition-all duration-300 ease-in-out translate-y-0"
         style="background-image: linear-gradient(to bottom, rgba(101, 48, 137, 0.95), rgba(101, 48, 137, 0.98));"
         aria-modal="true"
         role="dialog"
         aria-labelledby="mobile-results-title"
         x-data="{ 
           focusFirst() { 
             let focusable = this.$el.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex=\'-1\'])');
             if (focusable) focusable.focus();
           }
         }"
         x-init="focusFirst()"
         x-trap.inert.noscroll="true"
         x-on:keydown.escape="$wire.closeResults()">

        <div class="p-2.5 bg-black/20">
            <div class="flex items-center justify-between">
                <div>
                <h2 id="mobile-results-title" class="text-sm font-semibold text-white">
                    Resultados da Pesquisa
                </h2>
                <span class="text-xs font-mono text-white/80" aria-live="polite" aria-atomic="true">
                    {{ $totalResults }} {{ $totalResults === 1 ? 'local encontrado' : 'locais encontrados' }}
                    @if(!empty($search))
                        para "{{ $search }}"
                    @endif
                    @if(array_sum($typeFilters) > 0)
                        com filtros aplicados
                    @endif
                </span>
                </div>
                <div class="flex items-center gap-2">
                    @if (array_sum($typeFilters) > 0 || !empty($search))
                        <button wire:click="clearFilters"
                            class="flex items-center justify-center w-8 h-8 bg-black/30 hover:bg-black/50 rounded-full text-white/70 hover:text-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-secondary focus:ring-offset-1"
                            aria-label="Limpar todos os filtros">
                            @svg('heroicon-o-trash', 'w-4 h-4')
                        </button>
                    @endif
                    <button wire:click="closeResults"
                        class="flex items-center justify-center w-8 h-8 bg-black/30 hover:bg-black/50 rounded-full text-white/70 hover:text-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-secondary focus:ring-offset-1"
                        aria-label="Fechar resultados para melhor navegação no mapa">
                        @svg('heroicon-o-arrow-down', 'w-4 h-4')
                    </button>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto soft-scrollbar" style="height: calc(50vh - 70px);"
             x-data="{ 
               currentIndex: -1,
               navigate(direction) {
                 let cards = this.$el.querySelectorAll('[role=button]');
                 if (cards.length === 0) return;
                 if (direction === 'down') this.currentIndex = Math.min(this.currentIndex + 1, cards.length - 1);
                 if (direction === 'up') this.currentIndex = Math.max(this.currentIndex - 1, 0);
                 if (direction === 'home') this.currentIndex = 0;
                 if (direction === 'end') this.currentIndex = cards.length - 1;
                 cards[this.currentIndex].focus();
               }
             }"
             x-on:keydown.arrow-down.prevent="navigate('down')"
             x-on:keydown.arrow-up.prevent="navigate('up')"
             x-on:keydown.home.prevent="navigate('home')"
             x-on:keydown.end.prevent="navigate('end')">
            <div class="p-2 space-y-2">
                @forelse($results as $result)
                    <article
                        class="text-white border-b border-black/30 p-2 {{ $selectedLocationId == $result['id'] ? 'bg-white rounded-lg border-0 !text-primary' : '' }} last:border-0 group cursor-pointer focus:outline-none focus:ring-2 focus:ring-secondary focus:ring-offset-2 focus:ring-offset-primary rounded-lg transition-all duration-200"
                        data-location-id="{{ $result['id'] }}"
                        role="button"
                        tabindex="0"
                        aria-label="Visualizar {{ $result['name'] }} no mapa. Tipo: {{ $result['typeLabel'] }}. {{ isset($result['description']) ? \Str::limit($result['description'], 60) : 'Clique para mais detalhes.' }}"
                        aria-describedby="mobile-result-{{ $result['id'] }}-desc"
                        wire:click="focusLocation('{{ $result['id'] }}')"
                        wire:keydown.enter="focusLocation('{{ $result['id'] }}')"
                        wire:keydown.space="focusLocation('{{ $result['id'] }}')"
                        wire:loading.class="opacity-75 pointer-events-none"
                        wire:loading.attr="aria-busy"
                        title="{{ $result['name'] }} - {{ $result['typeLabel'] }}">

                        <div class="flex items-start justify-between space-x-4">
                            <img src="{{ asset('assets/images/'.$result['type']->value.'.png') }}" alt="{{$result['typeLabel']}}" class="w-6 h-auto" />
                            <div class="flex-1 space-y-2">
                                <div class="flex-1 space-y-1">
                                    <h3 class="font-semibold text-base leading-tight {{ $selectedLocationId == $result['id'] ? 'text-primary' : 'text-secondary' }}">
                                        {{ \Str::limit($result['name'], 48) }}
                                    </h3>
                                    <p id="mobile-result-{{ $result['id'] }}-desc" class="text-sm leading-tight text-opacity-85">
                                        {{ \Str::limit($result['description'], 72) ?? 'Acesse este local para mais detalhes...' }}
                                    </p>
                                </div>

                                @if (isset($result['images']) && count($result['images']) > 0)
                                    <div class="flex -space-x-2 mt-2">
                                        @foreach (array_slice($result['images'], 0, 2) as $index => $image)
                                            <div class="w-8 h-8 md:w-6 md:h-6 rounded-full overflow-hidden bg-black/25 border-2 {{$selectedLocationId == $result['id'] ? 'border-white' : 'border-primary'}} flex-shrink-0">
                                                <img src="{{ $image['url'] }}"
                                                    alt="Imagem {{ $index + 1 }} de {{ $result['name'] }}"
                                                    class="w-full h-full object-cover" loading="lazy"
                                                    onerror="this.style.display='block'; this.style.backgroundColor='rgba(0,0,0,0.3)'; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjMyIiBmaWxsPSIjNjUzMDg5IiBmaWxsLW9wYWNpdHk9IjAuNSIvPgo8cGF0aCBkPSJNOCAxMkw0IDE2TDggMjBNMjQgMTJMMjggMTZMMjQgMjAiIHN0cm9rZT0iI0NFRDg0MiIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPC9zdmc+Cg==';">
                                            </div>
                                        @endforeach
                                        @if (count($result['images']) > 2)
                                            <div class="w-8 h-8 md:w-6 md:h-6 rounded-full overflow-hidden bg-black/25 border-2 z-10 {{$selectedLocationId == $result['id'] ? 'border-white' : 'border-primary'}} flex items-center justify-center flex-shrink-0">
                                                <span class="text-xs text-white/70 font-bold">+{{ count($result['images']) - 2 }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="flex mt-2">
                                        <div class="w-8 h-8 md:w-6 md:h-6 rounded-full bg-black/25 border-2 {{$selectedLocationId == $result['id'] ? 'border-white' : 'border-primary'}} flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-shrink-0 self-start ml-auto group-hover:translate-x-1 transition-transform duration-200">
                                <x-heroicon-o-arrow-right class="w-4 h-4 {{ $selectedLocationId == $result['id'] ? 'text-primary group-hover:text-primary' : 'text-secondary' }}" aria-hidden="true" />
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

    @if((!empty($search) || array_sum($typeFilters) > 0) && !$resultsOpen && $totalResults > 0)
    <div class="fixed bottom-4 left-1/2 transform -translate-x-1/2 z-[998]" id="mobile-results-indicator">
        <button wire:click="openResults"
            class="flex items-center gap-2 bg-secondary text-primary px-4 py-2 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            aria-label="Mostrar {{ $totalResults }} {{ $totalResults === 1 ? 'resultado' : 'resultados' }}">
            @svg('heroicon-o-arrow-up', 'w-4 h-4')
            <span>{{ $totalResults }} {{ $totalResults === 1 ? 'resultado' : 'resultados' }}</span>
        </button>
    </div>
    @endif
</div>
