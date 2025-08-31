<!-- Desktop: Full sidebar -->
<aside
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
        <!-- Desktop header -->
        <header class="p-3 md:p-2 safe-area-top">
            <div class="mb-4">
                <img src="{{ asset('assets/images/logo.svg') }}" alt="Logo" class="w-20 md:w-24 m-0 md:m-2">
            </div>

            <div class="relative">
                <label for="search-input" class="sr-only">Pesquisar locais</label>
                <input type="search" id="search-input" wire:model.live.debounce.300ms="search"
                    placeholder="Pesquisar locais..."
                    class="bg-white w-full px-4 py-3 pr-10 text-sm md:text-base border-2 border-primary rounded-lg focus:outline-none focus:ring-4 focus:ring-black/25 transition-all duration-200"
                    aria-describedby="search-help">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    @svg('heroicon-o-magnifying-glass', 'w-5 h-5 text-primary')
                </div>
            </div>
            <div id="search-help" class="sr-only">
                Digite para pesquisar por nome de locais
            </div>
        </header>

        <!-- Desktop filters section -->
        <section class="px-3 py-2 md:p-2.5 bg-no-repeat bg-top bg-black/20">
            <fieldset class="space-y-3 md:space-y-2 mb-3 md:mb-2">
                <div class="flex items-center justify-between">
                    <legend class="text-base font-semibold text-white">
                        Filtrar por
                    </legend>
                    @if (array_sum($typeFilters) > 0 || !empty($search))
                        <button wire:click="clearFilters"
                            class="flex items-center justify-center w-8 h-8 md:w-auto md:h-auto text-sm text-primary font-semibold hover:underline cursor-pointer focus:outline-none focus:underline"
                            aria-label="Limpar todos os filtros">
                            @svg('heroicon-o-trash', 'w-5 h-5 text-white/80')
                        </button>
                    @endif
                </div>

                <div class="flex items-center gap-x-4" wire:key="filter-toggles">
                    <x-toggle-button wire:model.live="typeFilters.accessible" :value="$typeFilters['accessible']"
                        label="Acessível" trackClass="bg-black/20 border-white" thumbClass="bg-green-500"
                        labelClass="text-white text-sm" wire:key="filter-accessible" />

                    <x-toggle-button wire:model.live="typeFilters.non_accessible" :value="$typeFilters['non_accessible']"
                        label="Não Acessível" trackClass="bg-black/20 border-white" thumbClass="bg-amber-500"
                        labelClass="text-white text-sm" wire:key="filter-non-accessible" />
                </div>
            </fieldset>

            <div class="space-y-2">
                <p class="text-xs font-mono text-white" aria-live="polite">
                    Mostrando {{ $totalResults }} {{ $totalResults === 1 ? 'resultado' : 'resultados' }}
                </p>
            </div>
        </section>

        <!-- Desktop results section -->
        <main class="flex-1 overflow-y-auto soft-scrollbar p-2 md:p-2 safe-area-bottom">
            <div class="space-y-2" wire:key="results-{{ md5(json_encode($typeFilters) . $search) }}">
                @forelse($results as $result)
                    <article
                        class="text-white border-b border-black/30 p-2 pb-4 {{ $selectedLocationId == $result['id'] ? 'bg-white rounded-lg border-0 !text-primary' : '' }} last:border-0 group cursor-pointer"
                        data-location-id="{{ $result['id'] }}" role="button" tabindex="0"
                        aria-label="Ver {{ $result['name'] }} no mapa - {{ $result['typeLabel'] }}"
                        wire:click="focusLocation('{{ $result['id'] }}')"
                        wire:keydown.enter="focusLocation('{{ $result['id'] }}')"
                        wire:keydown.space="focusLocation('{{ $result['id'] }}')"
                        wire:loading.class="opacity-75 pointer-events-none"
                        title="{{ $result['name'] }} - {{ $result['typeLabel'] }}">
                        <div class="flex items-start justify-between space-x-3 md:space-x-2">
                            <div class="w-2 h-2 rounded-full mt-1 flex-shrink-0 bg-{{ $result['typeColor'] }}-500"
                                 aria-hidden="true">
                            </div>
                            <div class="flex-1 space-y-2">
                                <div class="flex-1 space-y-2">
                                    <h3 class="font-semibold text-base leading-tight">
                                        {{ \Str::limit($result['name'], 48) }}
                                    </h3>
                                    <p class="text-sm leading-tight text-opacity-85">
                                        {{ \Str::limit($result['description'], 64) ?? 'Acesse este local para mais detalhes...' }}
                                    </p>
                                </div>

                                @if (isset($result['images']) && count($result['images']) > 0)
                                    <div class="flex -space-x-2">
                                        @foreach (array_slice($result['images'], 0, 2) as $index => $image)
                                            <div class="w-8 h-8 rounded-full overflow-hidden bg-black/25 border-2 {{$selectedLocationId == $result['id'] ? 'border-white' : 'border-primary'}}">
                                                <img src="{{ $image['url'] }}"
                                                    alt="Imagem {{ $index + 1 }} de {{ $result['name'] }}"
                                                    class="w-full h-full object-cover" loading="lazy">
                                            </div>
                                        @endforeach
                                        @if (count($result['images']) > 2)
                                            <div class="w-8 h-8 rounded-full overflow-hidden bg-black/25 border-2 z-10 {{$selectedLocationId == $result['id'] ? 'border-white' : 'border-primary'}}">
                                                <span class="text-xs text-white/70 font-medium">+{{ count($result['images']) - 2 }}</span>
                                            </div>
                                        @endif
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
