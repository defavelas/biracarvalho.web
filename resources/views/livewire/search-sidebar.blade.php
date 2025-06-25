<aside
    class="absolute top-2 left-2 bottom-2 w-96 bg-secondary border-4 border-black/15 z-[100] shadow-xl transform transition-transform duration-300 ease-in-out rounded-lg overflow-hidden {{ $collapsed ? '-translate-x-full' : 'translate-x-0' }}"
    aria-label="Painel de pesquisa e filtros" role="complementary" aria-hidden="{{ $collapsed ? 'true' : 'false' }}">
    <div class="flex flex-col h-full">
        <header class="p-4 bg-secondary">
            <img src="{{ asset('assets/images/logo.svg') }}" alt="Logo" class="w-32 m-2 mb-4">
            <div class="relative">
                <label for="search-input" class="sr-only">Pesquisar locais</label>
                <input type="search" id="search-input" wire:model.live.debounce.300ms="search"
                    placeholder="Pesquisar locais..."
                    class="bg-white w-full px-4 py-3 pr-10 text-sm border-2 border-primary rounded-lg focus:outline-none focus:ring-4 focus:ring-black/25 focus:border-primary transition-all duration-200"
                    aria-describedby="search-help">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    @svg('heroicon-o-magnifying-glass', 'w-5 h-5 text-gray-400')
                </div>
            </div>
            <div id="search-help" class="sr-only">
                Digite para pesquisar por nome ou endereço de locais
            </div>
        </header>

        <section class="p-4 bg-no-repeat bg-cover bg-center bg-primary" style="background-image: url('{{ asset('assets/images/filter-bg.jpg') }}')">
            <fieldset class="space-y-2 mb-2">
                <legend class="text-base font-semibold text-secondary">Acessibilidade</legend>

                <div class="space-y-2">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" wire:model.live="accessibilityFilters.acessivel"
                            class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-secondary">Acessível</span>
                        </div>
                    </label>

                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" wire:model.live="accessibilityFilters.parcial_acessivel"
                            class="w-4 h-4 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            <span class="text-sm text-secondary">Parcialmente Acessível</span>
                        </div>
                    </label>

                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" wire:model.live="accessibilityFilters.nao_acessivel"
                            class="w-4 h-4 text-rose-600 border-gray-300 rounded focus:ring-rose-500">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-rose-500 rounded-full"></div>
                            <span class="text-sm text-secondary">Não Acessível</span>
                        </div>
                    </label>
                </div>
            </fieldset>

            <p class="text-sm text-secondary" aria-live="polite">
                {{ $totalResults }}
                {{ $totalResults === 1 ? 'resultado encontrado' : 'resultados encontrados' }}
            </p>

            @if (array_sum($accessibilityFilters) > 0 || !empty($search))
                    <button wire:click="clearFilters"
                        class="flex flex-row gap-2 items-center justify-center text-sm text-secondary font-semibold hover:underline cursor-pointer focus:outline-none focus:underline"
                        aria-label="Limpar todos os filtros">
                        @svg('heroicon-o-trash', 'w-4 h-4 text-secondary')
                        Limpar filtros
                    </button>
                @endif
        </section>

        <main class="flex-1 overflow-y-auto soft-scrollbar p-2">
            <div class="space-y-3">
                @forelse($results as $result)
                    <article
                        class="bg-black/25 border-2 {{ $selectedLocationId == $result['id'] ? 'border-primary shadow-md' : 'border-black/30' }} rounded-lg p-4 hover:shadow-md hover:border-primary cursor-pointer transition-all duration-200 group"
                        data-location-id="{{ $result['id'] }}" role="button" tabindex="0"
                        aria-label="Ver {{ $result['name'] }} no mapa - {{ $result['accessibility_level'] === 'acessivel' ? 'Acessível' : '' }}{{ $result['accessibility_level'] === 'parcial_acessivel' ? 'Parcialmente Acessível' : '' }}{{ $result['accessibility_level'] === 'nao_acessivel' ? 'Não Acessível' : '' }}"
                        wire:click="focusLocation('{{ $result['id'] }}')"
                        wire:keydown.enter="focusLocation('{{ $result['id'] }}')"
                        wire:keydown.space="focusLocation('{{ $result['id'] }}')">
                        <div class="flex items-start justify-between space-x-2">
                            <div class="flex-1 space-y-3">
                                <div class="flex items-start space-x-2">
                                    <div class="w-3 h-3 rounded-full mt-1 flex-shrink-0
                                        {{ $result['accessibility_level'] === 'acessivel' ? 'bg-green-500' : '' }}
                                        {{ $result['accessibility_level'] === 'parcial_acessivel' ? 'bg-yellow-500' : '' }}
                                        {{ $result['accessibility_level'] === 'nao_acessivel' ? 'bg-rose-500' : '' }}"
                                        aria-hidden="true">
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-primary text-sm leading-tight mb-1">
                                            {{ $result['name'] }}</h3>
                                        <p class="text-xs text-white/80">{{ $result['address'] }}</p>
                                    </div>
                                </div>

                                @if (isset($result['images']) && count($result['images']) > 0)
                                    <div class="flex space-x-1" aria-label="Imagens do local">
                                        @php
                                            $images = $result['images'];
                                            $totalImages = count($images);
                                            $displayImages = array_slice($images, 0, 3);
                                            $remainingCount = max(0, $totalImages - 3);
                                        @endphp

                                        @foreach ($displayImages as $index => $image)
                                            <div
                                                class="relative w-12 h-12 rounded-md overflow-hidden bg-black/25 flex-shrink-0">
                                                <img src="{{ $image }}"
                                                    alt="Imagem {{ $index + 1 }} de {{ $result['name'] }}"
                                                    class="w-full h-full object-cover" loading="lazy">
                                            </div>
                                        @endforeach

                                        @if ($remainingCount > 0)
                                            <div
                                                class="w-12 h-12 rounded-md bg-black/25 flex items-center justify-center flex-shrink-0">
                                                <span class="text-sm text-white/50 font-semibold"
                                                    aria-label="{{ $remainingCount }} imagens adicionais">
                                                    +{{ $remainingCount }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    {{-- Placeholder when no images --}}
                                    <div class="flex space-x-1">
                                        @for ($i = 0; $i < 3; $i++)
                                            <div
                                                class="w-12 h-12 rounded-md bg-black/25 flex items-center justify-center flex-shrink-0">
                                                @svg('heroicon-o-photo', 'w-5 h-5 text-white/50')
                                            </div>
                                        @endfor
                                    </div>
                                @endif

                                <div class="flex items-center justify-between">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 font-semibold rounded-full text-xs text-black/75
                                        {{ $result['accessibility_level'] === 'acessivel' ? 'bg-green-500' : '' }}
                                        {{ $result['accessibility_level'] === 'parcial_acessivel' ? 'bg-yellow-500' : '' }}
                                        {{ $result['accessibility_level'] === 'nao_acessivel' ? 'bg-rose-500' : '' }}">
                                        {{ $result['accessibility_level'] === 'acessivel' ? 'Acessível' : '' }}
                                        {{ $result['accessibility_level'] === 'parcial_acessivel' ? 'Parcialmente Acessível' : '' }}
                                        {{ $result['accessibility_level'] === 'nao_acessivel' ? 'Não Acessível' : '' }}
                                    </span>

                                    @if (isset($result['latitude']) && isset($result['longitude']))
                                        <span class="text-[10px] text-white/50 font-mono text-right"
                                            aria-label="Coordenadas: Latitude {{ number_format($result['latitude'], 4) }}, Longitude {{ number_format($result['longitude'], 4) }}">
                                            {{ number_format($result['latitude'], 4) }},
                                            {{ number_format($result['longitude'], 4) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div
                                class="flex-shrink-0 self-start ml-auto group-hover:translate-x-1 transition-transform duration-200">
                                @svg('heroicon-o-arrow-right', 'w-5 h-5 text-white/50 group-hover:text-primary', ['aria-hidden' => 'true'])
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="p-8 text-center">
                        <div class="mx-auto w-16 h-16 bg-black/25 rounded-full flex items-center justify-center mb-4">
                            @svg('heroicon-o-magnifying-glass', 'w-8 h-8 text-primary')
                        </div>
                        <h3 class="text-base font-semibold text-primary mb-1">Nenhum resultado encontrado</h3>
                        <p class="text-sm text-white/50">Tente ajustar seus filtros ou termo de pesquisa</p>
                    </div>
                @endforelse
            </div>
        </main>
    </div>
</aside>
