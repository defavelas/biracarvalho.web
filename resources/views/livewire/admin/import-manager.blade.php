<div>
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">Gerenciar Importações</h1>
            <p class="mt-2 text-sm text-gray-700">
                Gerencie as importações de arquivos CSV com dados de pesquisa de acessibilidade.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <button wire:click="openUploadModal" 
                    type="button" 
                    class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                Nova Importação
            </button>
            <button wire:click="refreshStatus" 
                    type="button" 
                    class="ml-3 inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                Atualizar
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div>
            <label for="date-filter" class="block text-sm font-medium text-gray-700">Data</label>
            <input wire:model.live="dateFilter" 
                   type="date" 
                   id="date-filter"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
        
        <x-select 
            wire:model.live="statusFilter"
            id="status-filter"
            for="status-filter"
            label="Status"
            placeholder="Todos os status"
            :options="collect($statusOptions)->map(fn($label, $value) => ['value' => $value, 'label' => $label])->values()->toArray()"
            valueField="value"
            labelField="label"
        />
    </div>

    <!-- Table -->
    <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Arquivo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Progresso
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Resultados
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Data/Duração
                                </th>
                                <th class="relative px-6 py-3">
                                    <span class="sr-only">Ações</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($imports as $import)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $import->original_filename }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $import->created_at->format('d/m/Y H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            @if($import->status === 'completed') bg-green-100 text-green-800
                                            @elseif($import->status === 'processing') bg-blue-100 text-blue-800
                                            @elseif($import->status === 'failed') bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800
                                            @endif">
                                            {{ $import->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($import->total_rows > 0)
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-indigo-600 h-2 rounded-full" 
                                                     style="width: {{ $import->progress_percentage }}%"></div>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $import->processed_rows }}/{{ $import->total_rows }} 
                                                ({{ $import->progress_percentage }}%)
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($import->isCompleted())
                                            <div class="space-y-1">
                                                <div class="text-green-600">✓ {{ $import->successful_rows }} sucessos</div>
                                                @if($import->failed_rows > 0)
                                                    <div class="text-red-600">✗ {{ $import->failed_rows }} falhas</div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>{{ $import->created_at->format('d/m/Y') }}</div>
                                        @if($import->duration)
                                            <div class="text-xs">{{ $import->duration }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex space-x-2">
                                            @if($import->isCompleted() && $import->status === 'failed')
                                                <button wire:click="retryImport({{ $import->id }})" 
                                                        class="text-indigo-600 hover:text-indigo-900">
                                                    Tentar Novamente
                                                </button>
                                            @endif
                                            
                                            @if($import->errors && count($import->errors) > 0)
                                                <button onclick="showErrors{{ $import->id }}()" 
                                                        class="text-yellow-600 hover:text-yellow-900">
                                                    Ver Erros
                                                </button>
                                            @endif
                                            
                                            <button wire:click="deleteImport({{ $import->id }})" 
                                                    wire:confirm="Tem certeza que deseja excluir esta importação?"
                                                    class="text-red-600 hover:text-red-900">
                                                Excluir
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                @if($import->errors && count($import->errors) > 0)
                                    <tr id="errors{{ $import->id }}" style="display: none;">
                                        <td colspan="6" class="px-6 py-4 bg-red-50">
                                            <div class="text-sm text-red-800">
                                                <strong>Erros encontrados:</strong>
                                                <ul class="mt-2 list-disc list-inside space-y-1">
                                                    @foreach(array_slice($import->errors, 0, 10) as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                    @if(count($import->errors) > 10)
                                                        <li class="text-gray-600">... e mais {{ count($import->errors) - 10 }} erros</li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>

                                    <script>
                                        function showErrors{{ $import->id }}() {
                                            const errorRow = document.getElementById('errors{{ $import->id }}');
                                            errorRow.style.display = errorRow.style.display === 'none' ? 'table-row' : 'none';
                                        }
                                    </script>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Nenhuma importação encontrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $imports->links() }}
    </div>

    <!-- Upload Modal -->
    @if($showUploadModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit="uploadCsv">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                        Nova Importação CSV
                                    </h3>
                                    
                                    <div class="mb-4">
                                        <label for="csv-file" class="block text-sm font-medium text-gray-700 mb-2">
                                            Arquivo CSV
                                        </label>
                                        <input wire:model="csvFile" 
                                               type="file" 
                                               id="csv-file"
                                               accept=".csv,.txt"
                                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                        @error('csvFile')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-blue-800">
                                                    Informações sobre o arquivo
                                                </h3>
                                                <div class="mt-2 text-sm text-blue-700">
                                                    <ul class="list-disc list-inside space-y-1">
                                                        <li>Aceita arquivos CSV de pesquisa de acessibilidade</li>
                                                        <li>Tamanho máximo: 50MB</li>
                                                        <li>O processamento será feito em segundo plano</li>
                                                        <li>Você pode acompanhar o progresso nesta página</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                <span wire:loading.remove>Enviar e Processar</span>
                                <span wire:loading>Enviando...</span>
                            </button>
                            <button wire:click="closeUploadModal" 
                                    type="button" 
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Auto-refresh for processing imports -->
    <script>
        document.addEventListener('livewire:init', () => {
            setInterval(() => {
                @this.call('refreshStatus');
            }, 5000); // Refresh every 5 seconds
        });
    </script>
</div>

