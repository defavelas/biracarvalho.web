<aside 
    class="absolute top-2 left-2 bottom-2 w-96 bg-white z-[100] shadow-xl transform transition-transform duration-300 ease-in-out rounded-lg overflow-hidden {{ $collapsed ? '-translate-x-full' : 'translate-x-0' }}" 
    aria-label="Painel de pesquisa e filtros"
    role="complementary"
    aria-hidden="{{ $collapsed ? 'true' : 'false' }}"
>
    <div class="flex flex-col h-full">
        <header class="p-4 border-b border-gray-200 bg-white">
            <div class="space-y-4">
                <img src="{{ asset('assets/images/logo.svg') }}" alt="Logo" class="w-32 m-2 mb-4">
                <div class="relative">
                    <label for="search-input" class="sr-only">Pesquisar locais</label>
                    <input type="search" id="search-input" wire:model.live.debounce.300ms="search"
                        placeholder="Pesquisar locais..."
                        class="w-full px-4 py-3 pr-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        aria-describedby="search-help">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        @svg('heroicon-o-magnifying-glass', 'w-5 h-5 text-gray-400')
                    </div>
                </div>
                <div id="search-help" class="sr-only">
                    Digite para pesquisar por nome ou endereço de locais
                </div>
            </div>
        </header>

        <section class="p-4 bg-gray-50 border-b border-gray-200">
            <fieldset class="space-y-3">
                <legend class="text-sm font-semibold text-gray-900">Filtros de Acessibilidade</legend>

                <div class="space-y-2">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" wire:model.live="accessibilityFilters.acessivel"
                            class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-gray-700">Acessível</span>
                        </div>
                    </label>

                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" wire:model.live="accessibilityFilters.parcial_acessivel"
                            class="w-4 h-4 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            <span class="text-sm text-gray-700">Parcialmente Acessível</span>
                        </div>
                    </label>

                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" wire:model.live="accessibilityFilters.nao_acessivel"
                            class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <span class="text-sm text-gray-700">Não Acessível</span>
                        </div>
                    </label>
                </div>

                @if (array_sum($accessibilityFilters) > 0 || !empty($search))
                    <button wire:click="clearFilters"
                        class="text-sm text-purple-600 hover:text-purple-800 focus:outline-none focus:underline"
                        aria-label="Limpar todos os filtros">
                        Limpar filtros
                    </button>
                @endif
            </fieldset>

            <div class="py-4">
                <p class="text-sm text-gray-600" aria-live="polite">
                    {{ $totalResults }}
                    {{ $totalResults === 1 ? 'resultado encontrado' : 'resultados encontrados' }}
                </p>
            </div>
        </section>

        <main class="flex-1 overflow-y-auto soft-scrollbar p-4">
            <div class="space-y-3">
                @forelse($results as $result)
                    <article 
                        class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md hover:border-purple-300 cursor-pointer transition-all duration-200 focus-within:ring-2 focus-within:ring-purple-500 focus-within:ring-offset-1"
                        data-location-id="{{ $result['id'] }}"
                        role="button"
                        tabindex="0"
                        aria-label="Ver {{ $result['name'] }} no mapa - {{ $result['accessibility_level'] === 'acessivel' ? 'Acessível' : '' }}{{ $result['accessibility_level'] === 'parcial_acessivel' ? 'Parcialmente Acessível' : '' }}{{ $result['accessibility_level'] === 'nao_acessivel' ? 'Não Acessível' : '' }}"
                        wire:click="focusLocation('{{ $result['id'] }}')"
                        wire:keydown.enter="focusLocation('{{ $result['id'] }}')"
                        wire:keydown.space="focusLocation('{{ $result['id'] }}')"
                    >
                        <div class="flex items-start justify-between space-x-3">
                            <div class="flex-1 space-y-3">
                                <div class="flex items-start space-x-2">
                                    <div
                                        class="w-3 h-3 rounded-full mt-1 flex-shrink-0
                                        {{ $result['accessibility_level'] === 'acessivel' ? 'bg-green-500' : '' }}
                                        {{ $result['accessibility_level'] === 'parcial_acessivel' ? 'bg-yellow-500' : '' }}
                                        {{ $result['accessibility_level'] === 'nao_acessivel' ? 'bg-red-500' : '' }}"
                                        aria-hidden="true">
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900 text-sm leading-tight">{{ $result['name'] }}</h3>
                                        <p class="text-sm text-gray-600 mt-1">{{ $result['address'] }}</p>
                                    </div>
                                </div>

                                {{-- Image Gallery --}}
                                @if(isset($result['images']) && count($result['images']) > 0)
                                    <div class="flex space-x-1" aria-label="Imagens do local">
                                        @php
                                            $images = $result['images'];
                                            $totalImages = count($images);
                                            $displayImages = array_slice($images, 0, 3);
                                            $remainingCount = max(0, $totalImages - 3);
                                        @endphp
                                        
                                        @foreach($displayImages as $index => $image)
                                            <div class="relative w-12 h-12 rounded-md overflow-hidden bg-gray-100 flex-shrink-0">
                                                <img src="{{ $image }}" 
                                                     alt="Imagem {{ $index + 1 }} de {{ $result['name'] }}" 
                                                     class="w-full h-full object-cover"
                                                     loading="lazy">
                                            </div>
                                        @endforeach
                                        
                                        @if($remainingCount > 0)
                                            <div class="w-12 h-12 rounded-md bg-gray-100 flex items-center justify-center flex-shrink-0">
                                                <span class="text-xs font-medium text-gray-500" aria-label="{{ $remainingCount }} imagens adicionais">
                                                    +{{ $remainingCount }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    {{-- Placeholder when no images --}}
                                    <div class="flex space-x-1">
                                        @for($i = 0; $i < 3; $i++)
                                            <div class="w-12 h-12 rounded-md bg-gray-100 flex items-center justify-center flex-shrink-0">
                                                @svg('heroicon-o-photo', 'w-5 h-5 text-gray-400')
                                            </div>
                                        @endfor
                                    </div>
                                @endif
                                
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        {{ $result['accessibility_level'] === 'acessivel' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $result['accessibility_level'] === 'parcial_acessivel' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $result['accessibility_level'] === 'nao_acessivel' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ $result['accessibility_level'] === 'acessivel' ? 'Acessível' : '' }}
                                        {{ $result['accessibility_level'] === 'parcial_acessivel' ? 'Parcialmente Acessível' : '' }}
                                        {{ $result['accessibility_level'] === 'nao_acessivel' ? 'Não Acessível' : '' }}
                                    </span>
                                    
                                    @if(isset($result['latitude']) && isset($result['longitude']))
                                        <span class="text-xs text-gray-500 font-mono" aria-label="Coordenadas: Latitude {{ number_format($result['latitude'], 4) }}, Longitude {{ number_format($result['longitude'], 4) }}">
                                            {{ number_format($result['latitude'], 4) }}, {{ number_format($result['longitude'], 4) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex-shrink-0 self-center ml-2">
                                @svg('heroicon-o-arrow-right', 'w-5 h-5 text-gray-400 group-hover:text-purple-600 transition-colors', ['aria-hidden' => 'true'])
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="p-8 text-center">
                        <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            @svg('heroicon-o-magnifying-glass', 'w-8 h-8 text-gray-400')
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">Nenhum resultado encontrado</h3>
                        <p class="text-sm text-gray-500">Tente ajustar seus filtros ou termo de pesquisa</p>
                    </div>
                @endforelse
            </div>
        </main>
    </div>
</aside>
