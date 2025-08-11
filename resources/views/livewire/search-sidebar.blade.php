<div>
    <!-- Mobile: Floating search bar -->
    <div class="md:hidden">
        <!-- Mobile floating search container -->
        <div class="fixed top-4 left-2 right-2 z-[1001] transform transition-all duration-300 ease-in-out translate-y-0 opacity-100"
             aria-label="Barra de pesquisa móvel" 
             aria-hidden="false">
            
            <!-- Compact search bar -->
            <div class="bg-primary rounded-lg shadow-xl border-4 border-black/15 overflow-hidden">
                <div class="p-2 bg-no-repeat bg-top" style="background-image: url('{{ asset('assets/images/search-bg.jpg') }}');">
                    <!-- Logo and search in one row -->
                    <div class="flex items-center gap-2 mb-2">
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

                    <!-- Filter buttons with labels (row 1) -->
                    <div class="flex flex-wrap justify-start items-center gap-2 my-2">
                            <button type="button" 
                                    wire:click="$toggle('accessibilityFilters.acessivel')"
                                    class="flex items-center gap-1 px-2 py-1 rounded-full border border-black/20 text-xs transition-all duration-200 {{ $accessibilityFilters['acessivel'] ? 'bg-green-500 border-green-500 text-black font-medium' : 'bg-black/20 text-white/80 font-medium' }}"
                                    aria-label="{{ $accessibilityFilters['acessivel'] ? 'Desativar filtro Acessível' : 'Ativar filtro Acessível' }}">
                                <div class="w-2 h-2 rounded-full {{ $accessibilityFilters['acessivel'] ? 'bg-white' : 'bg-green-500' }}"></div>
                                <span>Acessível</span>
                            </button>
                            <button type="button" 
                                    wire:click="$toggle('accessibilityFilters.parcial_acessivel')"
                                    class="flex items-center gap-1 px-2 py-1 rounded-full border border-black/20 text-xs transition-all duration-200 {{ $accessibilityFilters['parcial_acessivel'] ? 'bg-yellow-500 border-yellow-500 text-black font-medium' : 'bg-black/20 text-white/80 font-medium' }}"
                                    aria-label="{{ $accessibilityFilters['parcial_acessivel'] ? 'Desativar filtro Parcialmente Acessível' : 'Ativar filtro Parcialmente Acessível' }}">
                                <div class="w-2 h-2 rounded-full {{ $accessibilityFilters['parcial_acessivel'] ? 'bg-white' : 'bg-yellow-500' }}"></div>
                                <span>Parcial</span>
                            </button>
                            <button type="button" 
                                    wire:click="$toggle('accessibilityFilters.nao_acessivel')"
                                    class="flex items-center gap-1 px-2 py-1 rounded-full border border-black/20 text-xs transition-all duration-200 {{ $accessibilityFilters['nao_acessivel'] ? 'bg-accent-orange border-accent-orange text-black font-medium' : 'bg-black/20 text-white/80 font-medium' }}"
                                    aria-label="{{ $accessibilityFilters['nao_acessivel'] ? 'Desativar filtro Não Acessível' : 'Ativar filtro Não Acessível' }}">
                                <div class="w-2 h-2 rounded-full {{ $accessibilityFilters['nao_acessivel'] ? 'bg-white' : 'bg-accent-orange' }}"></div>
                                <span>Não Acessível</span>
                            </button>
                    </div>

                    <!-- Results count + reset (row 2) -->
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-mono text-white/90 min-h-8 flex items-center">
                            {{ $totalResults }} {{ $totalResults === 1 ? 'local' : 'locais' }}
                        </p>
                        @if (array_sum($accessibilityFilters) > 0 || !empty($search))
                            <button wire:click="clearFilters"
                                class="flex items-center justify-center w-8 h-8 bg-black/20 hover:bg-black/30 rounded-full text-white/80 hover:text-white transition-colors duration-200"
                                aria-label="Limpar todos os filtros">
                                @svg('heroicon-o-trash', 'w-4 h-4')
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile search results cards (50% screen height) -->
        @if((!empty($search) || array_sum($accessibilityFilters) > 0) && $resultsOpen)
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
                        @if (array_sum($accessibilityFilters) > 0 || !empty($search))
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
                            aria-label="Ver {{ $result['name'] }} no mapa - {{ $result['accessibility_level'] === 'acessivel' ? 'Acessível' : '' }}{{ $result['accessibility_level'] === 'parcial_acessivel' ? 'Parcialmente Acessível' : '' }}{{ $result['accessibility_level'] === 'nao_acessivel' ? 'Não Acessível' : '' }}"
                            wire:click="focusLocation('{{ $result['id'] }}')"
                            wire:keydown.enter="focusLocation('{{ $result['id'] }}')"
                            wire:keydown.space="focusLocation('{{ $result['id'] }}')"
                            wire:loading.class="opacity-75 pointer-events-none">
                            
                            <div class="flex items-start justify-between">
                                <div class="flex-1 space-y-2">
                                    <div class="flex items-start gap-2">
                                        <div class="w-3 h-3 rounded-full mt-1 flex-shrink-0
                                            {{ $result['accessibility_level'] === 'acessivel' ? 'bg-green-500' : '' }}
                                            {{ $result['accessibility_level'] === 'parcial_acessivel' ? 'bg-yellow-500' : '' }}
                                            {{ $result['accessibility_level'] === 'nao_acessivel' ? 'bg-accent-orange' : '' }}"
                                            aria-hidden="true">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-semibold text-secondary text-sm leading-tight mb-1 truncate">
                                                {{ $result['name'] }}
                                            </h4>
                                            <p class="text-xs text-white/70 leading-relaxed line-clamp-2">
                                                {{ $result['address'] }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 leading-4 font-medium rounded-full text-xs text-black/75
                                            {{ $result['accessibility_level'] === 'acessivel' ? 'bg-green-500' : '' }}
                                            {{ $result['accessibility_level'] === 'parcial_acessivel' ? 'bg-yellow-500' : '' }}
                                            {{ $result['accessibility_level'] === 'nao_acessivel' ? 'bg-accent-orange' : '' }}">
                                            {{ $result['accessibility_level'] === 'acessivel' ? 'Acessível' : '' }}
                                            {{ $result['accessibility_level'] === 'parcial_acessivel' ? 'Parcial' : '' }}
                                            {{ $result['accessibility_level'] === 'nao_acessivel' ? 'Não Acessível' : '' }}
                                        </span>

                                        @if (isset($result['images']) && count($result['images']) > 0)
                                            <div class="flex -space-x-1">
                                                @foreach (array_slice($result['images'], 0, 2) as $index => $image)
                                                    <div class="w-6 h-6 rounded-full overflow-hidden bg-black/25 border border-white/30">
                                                        <img src="{{ $image }}"
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
                            <p class="text-xs text-white/50">Tente ajustar seus filtros ou termo de pesquisa</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        @endif

        <!-- Results indicator when closed -->
        @if((!empty($search) || array_sum($accessibilityFilters) > 0) && !$resultsOpen && $totalResults > 0)
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
        <!-- Mobile-optimized header -->
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
                Digite para pesquisar por nome ou endereço de locais
            </div>
        </header>

        <!-- Mobile-optimized filters section -->
        <section class="px-3 py-2 md:p-2.5 bg-no-repeat bg-top bg-black/20">
            <fieldset class="space-y-3 md:space-y-2 mb-3 md:mb-2">
                <div class="flex items-center justify-between">
                    <legend class="text-base md:text-lg font-semibold text-white">
                        Acessibilidade
                    </legend>
                    @if (array_sum($accessibilityFilters) > 0 || !empty($search))
                        <button wire:click="clearFilters"
                            class="flex items-center justify-center w-8 h-8 md:w-auto md:h-auto text-sm text-primary font-semibold hover:underline cursor-pointer focus:outline-none focus:underline"
                            aria-label="Limpar todos os filtros">
                            @svg('heroicon-o-trash', 'w-5 h-5 text-white/80')
                        </button>
                    @endif
                </div>

                <div class="space-y-3 md:space-y-2 mb-3 md:mb-2" wire:key="filter-toggles">
                    <x-toggle-button wire:model.live="accessibilityFilters.acessivel" :value="$accessibilityFilters['acessivel']"
                        label="Acessível" trackClass="bg-black/20 border-white" thumbClass="bg-green-500"
                        labelClass="text-white text-sm" wire:key="filter-acessivel" />

                    <x-toggle-button wire:model.live="accessibilityFilters.parcial_acessivel" :value="$accessibilityFilters['parcial_acessivel']"
                        label="Parcialmente Acessível" trackClass="bg-black/20 border-white"
                        thumbClass="bg-yellow-500" labelClass="text-white text-sm" wire:key="filter-parcial" />

                    <x-toggle-button wire:model.live="accessibilityFilters.nao_acessivel" :value="$accessibilityFilters['nao_acessivel']"
                        label="Não Acessível" trackClass="bg-black/20 border-white" thumbClass="bg-accent-orange"
                        labelClass="text-white text-sm" wire:key="filter-nao-acessivel" />
                </div>
            </fieldset>

            <div class="space-y-2">
                <p class="text-xs md:text-sm font-mono text-white" aria-live="polite">
                    Mostrando {{ $totalResults }} {{ $totalResults === 1 ? 'resultado' : 'resultados' }}
                </p>
            </div>
        </section>

        <!-- Mobile-optimized results section -->
        <main class="flex-1 overflow-y-auto soft-scrollbar p-2 md:p-2 safe-area-bottom">
            <div class="space-y-2" wire:key="results-{{ md5(json_encode($accessibilityFilters) . $search) }}">
                @forelse($results as $result)
                    <article
                        class="bg-black/20 border-1 {{ $selectedLocationId == $result['id'] ? 'bg-secondary !text-primary shadow-md' : '!text-white border-black/30' }} rounded-lg p-3 md:p-2.5 hover:shadow-md hover:border-secondary cursor-pointer transition-all duration-200 group"
                        data-location-id="{{ $result['id'] }}" role="button" tabindex="0"
                        aria-label="Ver {{ $result['name'] }} no mapa - {{ $result['accessibility_level'] === 'acessivel' ? 'Acessível' : '' }}{{ $result['accessibility_level'] === 'parcial_acessivel' ? 'Parcialmente Acessível' : '' }}{{ $result['accessibility_level'] === 'nao_acessivel' ? 'Não Acessível' : '' }}"
                        wire:click="focusLocation('{{ $result['id'] }}')"
                        wire:keydown.enter="focusLocation('{{ $result['id'] }}')"
                        wire:keydown.space="focusLocation('{{ $result['id'] }}')"
                        wire:loading.class="opacity-75 pointer-events-none">
                        <div class="flex items-start justify-between space-x-3 md:space-x-2">
                            <div class="flex-1 space-y-2">
                                <div class="flex items-start space-x-2">
                                    <div class="w-4 h-4 md:w-3 md:h-3 rounded-full mt-1 flex-shrink-0
                                        {{ $result['accessibility_level'] === 'acessivel' ? 'bg-green-500' : '' }}
                                        {{ $result['accessibility_level'] === 'parcial_acessivel' ? 'bg-yellow-500' : '' }}
                                        {{ $result['accessibility_level'] === 'nao_acessivel' ? 'bg-accent-orange' : '' }}"
                                        aria-hidden="true">
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-base md:text-sm leading-tight mb-1">
                                            {{ $result['name'] }}
                                        </h3>
                                        <p class="text-sm md:text-xs leading-relaxed">
                                            {{ $result['address'] }}
                                        </p>
                                    </div>
                                </div>

                                @if (isset($result['images']) && count($result['images']) > 0)
                                    <div class="flex space-x-2" aria-label="Imagens do local">
                                        @php
                                            $images = $result['images'];
                                            $totalImages = count($images);
                                            $displayImages = array_slice($images, 0, 3);
                                            $remainingCount = max(0, $totalImages - 3);
                                        @endphp

                                        @foreach ($displayImages as $index => $image)
                                            <div class="relative w-14 h-14 md:w-12 md:h-12 rounded-md overflow-hidden bg-black/20 flex-shrink-0">
                                                <img src="{{ $image }}"
                                                    alt="Imagem {{ $index + 1 }} de {{ $result['name'] }}"
                                                    class="w-full h-full object-cover" loading="lazy">
                                            </div>
                                        @endforeach

                                        @if ($remainingCount > 0)
                                            <div class="w-14 h-14 md:w-12 md:h-12 rounded-md bg-black/20 flex items-center justify-center flex-shrink-0">
                                                <span class="text-sm text-white/50 font-medium"
                                                    aria-label="{{ $remainingCount }} imagens adicionais">
                                                    +{{ $remainingCount }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="flex space-x-2">
                                        @for ($i = 0; $i < 3; $i++)
                                            <div class="w-14 h-14 md:w-12 md:h-12 rounded-md bg-black/25 flex items-center justify-center flex-shrink-0">
                                                @svg('heroicon-o-photo', 'w-6 h-6 md:w-5 md:h-5 text-white/50')
                                            </div>
                                        @endfor
                                    </div>
                                @endif

                                <div class="flex items-center justify-between">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1.5 md:px-2 md:py-1 leading-5 font-semibold rounded-full text-sm md:text-xs text-black/75
                                        {{ $result['accessibility_level'] === 'acessivel' ? 'bg-green-500' : '' }}
                                        {{ $result['accessibility_level'] === 'parcial_acessivel' ? 'bg-yellow-500' : '' }}
                                        {{ $result['accessibility_level'] === 'nao_acessivel' ? 'bg-accent-orange' : '' }}">
                                        {{ $result['accessibility_level'] === 'acessivel' ? 'Acessível' : '' }}
                                        {{ $result['accessibility_level'] === 'parcial_acessivel' ? 'Parcialmente Acessível' : '' }}
                                        {{ $result['accessibility_level'] === 'nao_acessivel' ? 'Não Acessível' : '' }}
                                    </span>

                                    @if (isset($result['latitude']) && isset($result['longitude']))
                                        <span class="text-xs md:text-[10px] font-mono text-right hidden md:block"
                                            aria-label="Coordenadas: Latitude {{ number_format($result['latitude'], 4) }}, Longitude {{ number_format($result['longitude'], 4) }}">
                                            {{ number_format($result['latitude'], 4) }},
                                            {{ number_format($result['longitude'], 4) }}
                                        </span>
                                    @endif
                                </div>
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
                        <p class="text-sm text-white/50">Tente ajustar seus filtros ou termo de pesquisa</p>
                    </div>
                @endforelse
            </div>
        </main>
    </div>
</aside>
</div>
