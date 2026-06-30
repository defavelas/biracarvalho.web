<!-- Desktop: Full sidebar -->
<aside
    id="desktop-search-sidebar"
    class="hidden md:block absolute
           md:top-2 md:left-2 md:bottom-2 md:right-auto md:w-96 md:h-auto
           bg-primary bg-no-repeat bg-top border-0 md:border-4 border-black/15 z-[100] shadow-xl
           transform transition-transform duration-300 ease-in-out
           md:rounded-xl overflow-hidden
           {{ $collapsed ? '-translate-x-full' : 'translate-x-0' }}"
    aria-label="Painel de pesquisa e filtros"
    style="background-image: url('{{ asset('assets/images/search-bg.jpg') }}');"
    role="complementary"
    aria-hidden="{{ $collapsed ? 'true' : 'false' }}">

    <div class="flex flex-col h-full">
        <header class="p-3 md:p-2 safe-area-top">
            <h1 class="sr-only">Pesquisa de Locais</h1>
            <div class="mb-4">
                <img src="{{ asset('assets/images/logo.svg') }}" alt="Logo" class="w-20 md:w-24 m-0 md:m-2">
            </div>

            <div class="relative">
                <label for="search-input" class="sr-only">Pesquisar locais</label>
                <input type="search" id="search-input" wire:model.live.debounce.300ms="search"
                    placeholder="Pesquisar locais..."
                    class="bg-white w-full px-4 py-3 pr-10 text-sm md:text-base border-2 border-primary rounded-lg focus:outline-none focus:ring-4 focus:ring-black/25 focus:ring-offset-2 transition-all duration-200"
                    aria-describedby="search-help"
                    x-on:keydown.escape="$el.blur()">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    @svg('heroicon-o-magnifying-glass', 'w-5 h-5 text-primary')
                </div>
            </div>
            <div id="search-help" class="sr-only">
                Digite para pesquisar por nome de locais. Pressione Escape para sair do campo de pesquisa.
            </div>
        </header>

        <section class="px-3 py-2 md:p-2.5 bg-no-repeat bg-top bg-black/20 relative">
            @php($activeFilterCount = array_sum($typeFilters))

            <fieldset class="space-y-3 md:space-y-2 mb-3 md:mb-2" aria-labelledby="desktop-filters-legend">
                <legend id="desktop-filters-legend" class="text-base font-semibold text-white">
                    Filtrar por acessibilidade
                </legend>

                <div class="flex items-center justify-between gap-3">
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $activeFilterCount > 0 ? 'bg-secondary text-primary shadow-sm' : 'bg-white/10 text-white/80' }}">
                        {{ $activeFilterCount > 0 ? $activeFilterCount . ' ' . ($activeFilterCount === 1 ? 'filtro ativo' : 'filtros ativos') : 'Nenhum filtro ativo' }}
                    </span>

                    @if ($activeFilterCount > 0 || !empty($search))
                        <button wire:click="clearFilters"
                            class="inline-flex items-center justify-center rounded-full bg-white/10 p-2 text-white transition-colors duration-200 hover:bg-white/20 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-secondary focus-visible:ring-offset-2 focus-visible:ring-offset-primary"
                            aria-label="Limpar todos os filtros">
                            @svg('heroicon-o-trash', 'w-4 h-4')
                        </button>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-4" wire:key="filter-toggles-desktop">
                    <x-toggle-button id="filter-accessible-desktop" wire:model.live="typeFilters.accessible" :value="$typeFilters['accessible']"
                        label="Acessível" trackClass="bg-black/20 border-white" thumbClass="bg-green-500"
                        labelClass="text-white text-sm" />

                    <x-toggle-button id="filter-non-accessible-desktop" wire:model.live="typeFilters.non_accessible" :value="$typeFilters['non_accessible']"
                        label="Não Acessível" trackClass="bg-black/20 border-white" thumbClass="bg-amber-500"
                        labelClass="text-white text-sm" />
                </div>
            </fieldset>

            <div class="space-y-2">
                <p class="text-xs font-mono text-white" aria-live="polite" aria-atomic="true">
                    Mostrando {{ $totalResults }} {{ $totalResults === 1 ? 'resultado' : 'resultados' }}
                    @if(!empty($search))
                        para "{{ $search }}"
                    @endif
                    @if(array_sum($typeFilters) > 0)
                        com filtros aplicados
                    @endif
                </p>
            </div>
        </section>

        <main class="flex-1 overflow-y-auto soft-scrollbar p-2 md:p-2 safe-area-bottom"
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
            <h2 class="sr-only">Resultados da Pesquisa</h2>
            <div class="space-y-2" wire:key="results-{{ md5(json_encode($typeFilters) . $search) }}">
                @forelse($results as $result)
                    <article
                        class="text-white border-b border-black/30 p-2 pb-4 {{ $selectedLocationId == $result['id'] ? 'bg-white rounded-lg border-0 !text-primary ring-2 ring-secondary shadow-sm' : '' }} last:border-0 group cursor-pointer focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-secondary focus-visible:ring-offset-2 focus-visible:ring-offset-primary rounded-lg transition-all duration-200"
                        data-location-id="{{ $result['id'] }}" role="button" tabindex="0"
                        data-sidebar-result="true"
                        aria-label="Visualizar {{ $result['name'] }} no mapa. Tipo: {{ $result['typeLabel'] }}. {{ isset($result['description']) ? \Str::limit($result['description'], 60) : 'Clique para mais detalhes.' }}"
                        aria-describedby="result-{{ $result['id'] }}-desc"
                        wire:click="focusLocation('{{ $result['id'] }}')"
                        wire:keydown.enter="focusLocation('{{ $result['id'] }}')"
                        wire:keydown.space="focusLocation('{{ $result['id'] }}')"
                        wire:loading.class="opacity-75 pointer-events-none"
                        wire:loading.attr="aria-busy"
                        title="{{ $result['name'] }} - {{ $result['typeLabel'] }}">
                        <div class="flex items-start justify-between space-x-3 md:space-x-4">
                            <img src="{{ asset('assets/images/'.$result['type']->value.'.png') }}" alt="{{$result['typeLabel']}}" class="w-6 h-auto" />
                            <div class="flex-1 space-y-2">
                                <div class="flex-1 space-y-2">
                                    <h3 class="font-semibold text-base leading-tight {{ $selectedLocationId == $result['id'] ? 'text-primary' : 'text-secondary' }}">
                                        {{ \Str::limit($result['name'], 48) }}
                                    </h3>
                                    <p id="result-{{ $result['id'] }}-desc" class="text-sm leading-tight text-opacity-85">
                                        {{ \Str::limit($result['description'], 72) ?? 'Acesse este local para mais detalhes...' }}
                                    </p>
                                </div>

                                @if (isset($result['images']) && count($result['images']) > 0)
                                    <div class="flex -space-x-2 mt-2">
                                        @foreach (array_slice($result['images'], 0, 2) as $index => $image)
                                            <div class="w-8 h-8 rounded-full overflow-hidden bg-black/25 border-2 {{$selectedLocationId == $result['id'] ? 'border-white' : 'border-primary'}} flex-shrink-0">
                                                <img src="{{ $image['url'] }}"
                                                    alt="{{ $image['alt'] ?? 'Prévia fotográfica de ' . $result['name'] }}"
                                                    class="w-full h-full object-cover" loading="lazy"
                                                    onerror="this.style.display='block'; this.style.backgroundColor='rgba(0,0,0,0.3)'; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjMyIiBmaWxsPSIjNjUzMDg5IiBmaWxsLW9wYWNpdHk9IjAuNSIvPgo8cGF0aCBkPSJNOCAxMkw0IDE2TDggMjBNMjQgMTJMMjggMTZMMjQgMjAiIHN0cm9rZT0iI0NFRDg0MiIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPC9zdmc+Cg==';">
                                            </div>
                                        @endforeach
                                        @if (count($result['images']) > 2)
                                            <div class="w-8 h-8 rounded-full overflow-hidden bg-black/25 border-2 z-10 {{$selectedLocationId == $result['id'] ? 'border-white' : 'border-primary'}} flex items-center justify-center flex-shrink-0">
                                                <span class="text-xs text-white/70 font-bold">+{{ count($result['images']) - 2 }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="flex mt-2">
                                        <div class="w-8 h-8 rounded-full bg-black/25 border-2 {{$selectedLocationId == $result['id'] ? 'border-white' : 'border-primary'}} flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-shrink-0 self-start ml-auto group-hover:translate-x-1 transition-transform duration-200">
                                <x-heroicon-o-arrow-right class="w-5 h-5 {{ $selectedLocationId == $result['id'] ? 'text-primary group-hover:text-primary' : 'text-secondary' }}" aria-hidden="true" />
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="p-8 text-center">
                        <div class="mx-auto w-16 h-16 bg-black/25 rounded-full flex items-center justify-center mb-4">
                            @svg('heroicon-o-magnifying-glass', 'w-8 h-8 text-secondary')
                        </div>
                        <h3 class="text-base font-semibold text-secondary mb-1">Nenhum resultado encontrado</h3>
                        <p class="text-sm text-white/50">Tente ajustar seus filtros de acessibilidade ou termo de pesquisa</p>
                    </div>
                @endforelse
            </div>
        </main>
    </div>
</aside>
