<aside 
    class="absolute top-0 left-0 w-96 h-screen bg-white z-[1000] shadow-xl transform transition-transform duration-300 ease-in-out {{ $collapsed ? '-translate-x-full' : 'translate-x-0' }}" 
    aria-label="Painel de pesquisa e filtros"
    role="complementary"
    aria-hidden="{{ $collapsed ? 'true' : 'false' }}"
>
    <div class="flex flex-col h-full">
        <!-- Search Header -->
        <header class="p-4 border-b border-gray-200 bg-white">
            <div class="space-y-4">
                <!-- Search Input -->
                <div class="relative">
                    <label for="search-input" class="sr-only">Pesquisar locais</label>
                    <input type="search" id="search-input" wire:model.live.debounce.300ms="search"
                        placeholder="Pesquisar locais..."
                        class="w-full px-4 py-3 pr-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        aria-describedby="search-help">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
                <div id="search-help" class="sr-only">
                    Digite para pesquisar por nome ou endereço de locais
                </div>

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

                <div class="pt-2 border-t border-gray-100">
                    <p class="text-sm text-gray-600" aria-live="polite">
                        {{ $totalResults }}
                        {{ $totalResults === 1 ? 'resultado encontrado' : 'resultados encontrados' }}
                    </p>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto soft-scrollbar">
            <div class="divide-y divide-gray-100">
                @forelse($results as $result)
                    <article class="p-4 hover:bg-gray-50 cursor-pointer transition-colors"
                        data-location-id="{{ $result['id'] }}">
                        <div class="space-y-2">
                            <div class="flex items-start justify-between">
                                <h3 class="font-semibold text-gray-900 text-sm">{{ $result['name'] }}</h3>
                                <div class="flex items-center space-x-1 ml-2">
                                    <div
                                        class="w-2 h-2 rounded-full 
                                        {{ $result['accessibility_level'] === 'acessivel' ? 'bg-green-500' : '' }}
                                        {{ $result['accessibility_level'] === 'parcial_acessivel' ? 'bg-yellow-500' : '' }}
                                        {{ $result['accessibility_level'] === 'nao_acessivel' ? 'bg-red-500' : '' }}
                                    ">
                                    </div>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600">{{ $result['address'] }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $result['accessibility_level'] === 'acessivel' ? 'Acessível' : '' }}
                                {{ $result['accessibility_level'] === 'parcial_acessivel' ? 'Parcialmente Acessível' : '' }}
                                {{ $result['accessibility_level'] === 'nao_acessivel' ? 'Não Acessível' : '' }}
                            </p>
                        </div>
                    </article>
                @empty
                    <div class="p-8 text-center">
                        <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">Nenhum resultado encontrado</h3>
                        <p class="text-sm text-gray-500">Tente ajustar seus filtros ou termo de pesquisa</p>
                    </div>
                @endforelse
            </div>
        </main>
    </div>
</aside>
