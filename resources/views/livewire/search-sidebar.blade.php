<aside
    class="absolute top-2 left-2 bottom-2 w-96 bg-primary bg-no-repeat bg-top border-4 border-black/15 z-[100] shadow-xl transform transition-transform duration-300 ease-in-out rounded-xl overflow-hidden {{ $collapsed ? '-translate-x-full' : 'translate-x-0' }}"
    aria-label="Painel de pesquisa e filtros" style="background-image: url('{{ asset('assets/images/search-bg.jpg') }}');" role="complementary" aria-hidden="{{ $collapsed ? 'true' : 'false' }}">
    <div class="flex flex-col h-full">
        <header class="p-2">
            <img src="{{ asset('assets/images/logo.svg') }}" alt="Logo" class="w-24 m-2 mb-4">
            <div class="relative">
                <label for="search-input" class="sr-only">Pesquisar locais</label>
                <input type="search" id="search-input" wire:model.live.debounce.300ms="search"
                    placeholder="Pesquisar locais..."
                    class="bg-white w-full px-4 py-3 pr-10 text-sm border-2 border-primary rounded-lg focus:outline-none focus:ring-4 focus:ring-black/25 transition-all duration-200"
                    aria-describedby="search-help">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    @svg('heroicon-o-magnifying-glass', 'w-5 h-5 text-primary')
                </div>
            </div>
            <div id="search-help" class="sr-only">
                Digite para pesquisar por nome ou endereço de locais
            </div>
        </header>

        <section class="p-2.5 bg-no-repeat bg-top bg-black/20">
            <fieldset class="space-y-2 mb-2">
                <div class="flex items-center justify-between">
                    <legend class="text-base font-semibold text-white">
                        Acessibilidade
                    </legend>
                    @if (array_sum($accessibilityFilters) > 0 || !empty($search))
                        <button wire:click="clearFilters"
                            class="items-center justify-center text-sm text-primary font-semibold hover:underline cursor-pointer focus:outline-none focus:underline"
                            aria-label="Limpar todos os filtros">
                            @svg('heroicon-o-trash', 'w-5 h-5 text-primary')
                        </button>
                    @endif
                </div>

                <div class="space-y-2 mb-2">
                    <x-toggle-button wire:model.live="accessibilityFilters.acessivel" :value="$accessibilityFilters['acessivel']"
                        label="Acessível" trackClass="bg-primary border-secondary" thumbClass="bg-green-500"
                        labelClass="text-secondary" />

                    <x-toggle-button wire:model.live="accessibilityFilters.parcial_acessivel" :value="$accessibilityFilters['parcial_acessivel']"
                        label="Parcialmente Acessível" trackClass="bg-primary border-secondary"
                        thumbClass="bg-yellow-500" labelClass="text-secondary" />

                    <x-toggle-button wire:model.live="accessibilityFilters.nao_acessivel" :value="$accessibilityFilters['nao_acessivel']"
                        label="Não Acessível" trackClass="bg-primary border-secondary" thumbClass="bg-rose-500"
                        labelClass="text-secondary" />
                </div>
            </fieldset>

            <div class="space-y-2">
                <p class="text-xs font-mono text-white" aria-live="polite">
                    Mostrando {{ $totalResults }} {{ $totalResults === 1 ? 'resultado' : 'resultados' }}
                </p>
            </div>
        </section>

        <main class="flex-1 overflow-y-auto soft-scrollbar p-2">
            <div class="space-y-2">
                @forelse($results as $result)
                    <article
                        class="bg-black/25 border-2 {{ $selectedLocationId == $result['id'] ? 'border-secondary shadow-md bg-black/35' : 'border-black/30' }} rounded-lg p-2.5 hover:shadow-md hover:border-secondary cursor-pointer transition-all duration-200 group"
                        data-location-id="{{ $result['id'] }}" role="button" tabindex="0"
                        aria-label="Ver {{ $result['name'] }} no mapa - {{ $result['accessibility_level'] === 'acessivel' ? 'Acessível' : '' }}{{ $result['accessibility_level'] === 'parcial_acessivel' ? 'Parcialmente Acessível' : '' }}{{ $result['accessibility_level'] === 'nao_acessivel' ? 'Não Acessível' : '' }}"
                        wire:click="focusLocation('{{ $result['id'] }}')"
                        wire:keydown.enter="focusLocation('{{ $result['id'] }}')"
                        wire:keydown.space="focusLocation('{{ $result['id'] }}')"
                        wire:loading.class="opacity-75 pointer-events-none">
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
                                        <h3 class="font-semibold text-secondary text-sm leading-tight mb-1">
                                            {{ $result['name'] }}
                                        </h3>
                                        <p class="text-xs text-white/80">
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
                                    <div class="flex space-x-2">
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
                                        class="inline-flex items-center px-2 leading-5 font-semibold rounded-full text-xs text-black/75
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
                                @svg('heroicon-o-arrow-right', 'w-4 h-4 text-white/50 group-hover:text-secondary', ['aria-hidden' => 'true'])
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
