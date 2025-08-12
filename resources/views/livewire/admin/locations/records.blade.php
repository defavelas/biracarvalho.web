<div>
    <div class="mb-4">
        <div class="sm:flex sm:items-center sm:justify-between">
            <header class="sm:flex-auto">
                <h1 class="text-2xl font-bold text-white mb-2 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Locais de Acessibilidade
                </h1>
                <p class="text-white/80 text-sm">
                    Gerencie os locais de acessibilidade e mobilidade urbana do programa.
                </p>
            </header>
            
            <div class="mt-4 sm:mt-0 sm:flex-none">
                <button wire:click="create" 
                        type="button" 
                        class="inline-flex items-center justify-center rounded-md bg-secondary hover:bg-secondary/90 px-4 py-2 text-sm font-semibold text-primary shadow-lg transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-secondary/25 transform hover:scale-105 cursor-pointer">
                    @svg('heroicon-o-plus', 'w-6 h-6')
                </button>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 mb-6 border border-white/10">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label for="search" class="block text-sm font-medium text-white/90 mb-2">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Buscar
                </label>
                <input wire:model.live.debounce.300ms="search" 
                       type="text" 
                       id="search"
                       placeholder="Nome, endereço ou descrição..."
                       class="block w-full rounded-lg bg-white/10 border border-white/20 text-white placeholder-white/50 px-4 py-2.5 focus:outline-none focus:ring-4 focus:ring-secondary/25 focus:border-secondary/50 transition-all duration-200">
            </div>
            
            <div>
                <label for="type-filter" class="block text-sm font-medium text-white/90 mb-2">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Tipo
                </label>
                <select wire:model.live="typeFilter" 
                        id="type-filter"
                        class="block w-full rounded-lg bg-white/10 border border-white/20 text-white px-4 py-2.5 focus:outline-none focus:ring-4 focus:ring-secondary/25 focus:border-secondary/50 transition-all duration-200">
                    <option value="">Todos os tipos</option>
                    @foreach($locationTypes as $value => $label)
                        <option value="{{ $value }}" class="bg-primary text-white">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-end">
                <div class="flex-1">
                    <div class="text-sm font-medium text-white/90 mb-2">Total de locais</div>
                    <div class="text-2xl font-bold text-secondary">{{ $locations->total() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Tipo
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white/90 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Endereço
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
                                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full 
                                    @if($location->type->value === 'accessible') bg-green-500/20 text-green-300 border border-green-500/30
                                    @elseif($location->type->value === 'partially_accessible') bg-yellow-500/20 text-yellow-300 border border-yellow-500/30
                                    @else bg-red-500/20 text-red-300 border border-red-500/30
                                    @endif" aria-label="Tipo: {{ $location->type->label() }}">
                                    {{ $location->type->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-white/80">
                                <div class="max-w-xs truncate" title="{{ $location->address }}">
                                    {{ Str::limit($location->address, 40) }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($location->isPublished())
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
                                    <button wire:click="edit({{ $location->id }})" 
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-secondary hover:text-white bg-secondary/10 hover:bg-secondary/20 rounded-lg border border-secondary/30 transition-all duration-200">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Editar
                                    </button>
                                    <button wire:click="confirmDelete({{ $location->id }})" 
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

    <!-- Pagination -->
    <div class="mt-8 flex justify-center">
        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
            {{ $locations->links() }}
        </div>
    </div>

    <!-- Create Modal -->
    @livewire('admin.locations.create')

    <!-- Edit Modal -->
    @if($showEditModal && $selectedLocation)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data>
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"></div>
                
                <div class="inline-block align-bottom bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
                     x-transition:enter="ease-out duration-400"
                     x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100">
                    @livewire('admin.locations.edit', ['location' => $selectedLocation], key($selectedLocation->id))
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
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

