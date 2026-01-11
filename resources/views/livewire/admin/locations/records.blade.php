<div>
    <div class="mb-4">
        <div class="sm:flex sm:items-center sm:justify-between">
            <header class="sm:flex-auto">
                <h1 class="text-2xl font-bold text-white flex items-center">
                    <svg class="w-6 h-6 mr-2 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Locais de Acessibilidade
                </h1>
                <p class="text-white/80 text-base">
                    Gerencie os locais de acessibilidade e mobilidade urbana do programa.
                </p>
            </header>

        </div>
    </div>

    <div class="bg-black/15 p-4 mb-4 rounded-md">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <x-input
                wire:model.live.debounce.300ms="search"
                label="Buscar"
                placeholder="Nome, endereço ou descrição..."
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>'
                id="search"
                for="search"
            />

            <x-select
                wire:model.live="typeFilter"
                label="Categoria"
                placeholder="Todas as categorias"
                theme="dark"
                :options="collect([['value' => '', 'label' => 'Todas as categorias']])->merge(collect($categories)->map(fn($label, $value) => ['value' => $value, 'label' => $label]))"
                id="type-filter"
                for="type-filter"
            />

            <x-select
                wire:model.live="statusFilter"
                label="Status"
                placeholder="Todos os status"
                theme="dark"
                :options="[
                    ['value' => '', 'label' => 'Todos os status'],
                    ['value' => 'pending', 'label' => 'Pendente'],
                    ['value' => 'approved', 'label' => 'Aprovado']
                ]"
                id="status-filter"
                for="status-filter"
            />

            <div class="flex items-end">
                <div class="flex-1">
                    <div class="text-sm font-medium text-white/90 mb-2">Total de locais</div>
                    <div class="text-2xl font-bold text-secondary">{{ $locations->total() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/10">
                <thead class="bg-white/10">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white/90 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a1.994 1.994 0 01-1.414.586H7a4 4 0 01-4-4V7a4 4 0 014-4z"/>
                                </svg>
                                Nome
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white/90 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a1.994 1.994 0 01-1.414.586H7a4 4 0 01-4-4V7a4 4 0 014-4z"/>
                                </svg>
                                Categoria
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white/90 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Coordenadas
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white/90 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Status
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white/90 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Imagens
                            </div>
                        </th>
                        <th class="relative px-6 py-4">
                            <span class="sr-only">Ações</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($locations as $location)
                        <tr class="hover:bg-white/5 transition-colors duration-200">
                            <td class="px-6 py-4 text-sm font-medium text-white">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-secondary rounded-full mr-3 flex-shrink-0"></div>
                                    {{ $location->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full text-white" style="background-color: {{ $location->type->color() }}">
                                    {{ $location->type->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-white/80">
                                <div class="text-xs">
                                    {{ number_format($location->latitude, 6) }}, {{ number_format($location->longitude, 6) }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($location->isApproved())
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-secondary/20 text-secondary border border-secondary/30">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Publicado
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-white/10 text-white/70 border border-white/20">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
                                        </svg>
                                        Rascunho
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-white/80">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $location->images->count() }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    @if($location->isPending())
                                        <button wire:click="approve('{{ $location->id }}')"
                                                wire:confirm="Tem certeza que deseja aprovar este registro?"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-300 hover:text-white bg-green-500/10 hover:bg-green-500/20 rounded-lg border border-green-500/30 transition-all duration-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Aprovar
                                        </button>
                                    @endif
                                    <button wire:click="confirmDelete('{{ $location->id }}')"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-300 hover:text-white bg-red-500/10 hover:bg-red-500/20 rounded-lg border border-red-500/30 transition-all duration-200">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Excluir
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-white/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <p class="text-white/60 text-sm">Nenhum local encontrado.</p>
                                    <p class="text-white/40 text-xs mt-1">Tente ajustar os filtros ou criar um novo local.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8 flex justify-center">
        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
            {{ $locations->links() }}
        </div>
    </div>

    @if($showDeleteModal && $selectedLocation)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data>
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"></div>

                <div class="inline-block align-bottom bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full"
                     x-transition:enter="ease-out duration-400"
                     x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100">
                    <div class="p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-500/20 border border-red-500/30">
                                <svg class="h-6 w-6 text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg leading-6 font-semibold text-white mb-2">
                                    Confirmar Exclusão
                                </h3>
                                <p class="text-sm text-white/80">
                                    Tem certeza que deseja excluir o local "<strong class="text-secondary">{{ $selectedLocation->name }}</strong>"?
                                    Esta ação não pode ser desfeita e todos os dados associados serão permanentemente removidos.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white/5 px-6 py-4 flex flex-row-reverse gap-3">
                        <button wire:click="delete"
                                type="button"
                                class="inline-flex items-center justify-center rounded-lg px-6 py-3 bg-red-500 hover:bg-red-600 text-white font-semibold text-sm shadow-lg transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-red-500/25">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Confirmar Exclusão
                        </button>
                        <button wire:click="closeModals"
                                type="button"
                                class="inline-flex items-center justify-center rounded-lg px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-medium text-sm border border-white/20 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-white/25">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
