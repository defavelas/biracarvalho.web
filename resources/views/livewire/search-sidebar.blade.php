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
