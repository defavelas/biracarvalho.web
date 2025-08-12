<div>
    <!-- Header Section -->
    <div class="mb-4">
        <div class="flex items-center justify-between">
            <header>
                <h1 class="text-2xl font-bold text-white flex items-center">
                    <svg class="w-6 h-6 mr-3 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                    Importação de Dados
                </h1>
                <p class="text-white/80 text-base">
                    Importe locais de acessibilidade em lote através de arquivos CSV e acompanhe o histórico de
                    processamento.
                </p>
            </header>

            <div class="mt-4 sm:mt-0 sm:flex-none">
                <button wire:click="openImportModal" 
                        type="button" 
                        class="inline-flex items-center justify-center rounded-md bg-secondary hover:bg-secondary/90 px-4 py-2 text-sm font-semibold text-primary shadow-lg transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-secondary/25 transform hover:scale-105 cursor-pointer">
                    @svg('heroicon-o-plus', 'w-6 h-6')
                </button>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-black/15 p-4 mb-4 rounded-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 id="historico-title" class="text-lg font-semibold text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Histórico de Importações
            </h2>
            <div class="flex flex-col sm:flex-row gap-3">
                <x-select 
                    wire:model.live="statusFilter"
                    label="Status"
                    placeholder="Todos os status"
                    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                    :options="collect($statusOptions)->map(fn($label, $value) => ['value' => $value, 'label' => $label])->prepend(['value' => '', 'label' => 'Todos os status'])->values()->toArray()"
                    id="status-filter"
                    for="status-filter"
                />
                
                <x-input 
                    wire:model.live="searchDate"
                    type="date"
                    label="Por data"
                    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                    id="search-date"
                    for="search-date"
                />
            </div>
        </div>
    </div>

    <!-- Import History Table -->
    <div class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white/10">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white/90 uppercase tracking-wider">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Arquivo
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white/90 uppercase tracking-wider">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Status
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white/90 uppercase tracking-wider">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Processados
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white/90 uppercase tracking-wider">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Ignorados
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white/90 uppercase tracking-wider">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Início
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white/90 uppercase tracking-wider">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Término
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white/90 uppercase tracking-wider">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Criado em
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($imports as $import)
                        <tr class="hover:bg-white/5 transition-all duration-200">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-white">{{ $import->filename }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusConfig = [
                                        'pending' => [
                                            'bg' => 'bg-yellow-500/20',
                                            'text' => 'text-yellow-300',
                                            'border' => 'border-yellow-500/30',
                                            'icon' =>
                                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                                        ],
                                        'processing' => [
                                            'bg' => 'bg-blue-500/20',
                                            'text' => 'text-blue-300',
                                            'border' => 'border-blue-500/30',
                                            'icon' =>
                                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>',
                                        ],
                                        'completed' => [
                                            'bg' => 'bg-green-500/20',
                                            'text' => 'text-green-300',
                                            'border' => 'border-green-500/30',
                                            'icon' =>
                                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                                        ],
                                        'failed' => [
                                            'bg' => 'bg-red-500/20',
                                            'text' => 'text-red-300',
                                            'border' => 'border-red-500/30',
                                            'icon' =>
                                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                                        ],
                                    ];
                                    $config = $statusConfig[$import->status->value] ?? [
                                        'bg' => 'bg-white/10',
                                        'text' => 'text-white/70',
                                        'border' => 'border-white/20',
                                        'icon' =>
                                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                                    ];
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-full {{ $config['bg'] }} {{ $config['text'] }} border {{ $config['border'] }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        {!! $config['icon'] !!}
                                    </svg>
                                    {{ $import->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    <span class="text-sm text-white/80">{{ $import->processed_rows }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-sm text-white/80">{{ $import->skipped_rows }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-white/80">
                                    {{ optional($import->started_at)->format('d/m/Y H:i') ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-white/80">
                                    {{ optional($import->finished_at)->format('d/m/Y H:i') ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-white/80">
                                    {{ $import->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-white/30" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                    </svg>
                                    <h3 class="mt-4 text-sm font-medium text-white/60">Nenhuma importação encontrada</h3>
                                    <p class="mt-1 text-sm text-white/40">Faça sua primeira importação para começar.</p>
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
            {{ $imports->links() }}
        </div>
    </div>


    <div class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto" x-data="{ show: @entangle('showImportModal') }"
        x-show="show" x-cloak>
        <!-- Backdrop -->
        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/80 backdrop-blur-sm" wire:click="closeImportModal"></div>

        <!-- Modal -->
        <div x-show="show" x-transition:enter="ease-out duration-400 delay-150"
            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-8 scale-95"
            class="relative w-full max-w-2xl mx-4 bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl shadow-2xl overflow-hidden"
            x-trap.noscroll="show">
            <form wire:submit="import">
                <!-- Header -->
                <header class="px-6 py-4 border-b border-white/10">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-secondary/20 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">Nova Importação CSV</h3>
                                <p class="text-sm text-white/70">Envie um arquivo CSV para importar locais</p>
                            </div>
                        </div>
                        <button wire:click="closeImportModal" type="button"
                            class="text-white/60 hover:text-white p-2 rounded-lg hover:bg-white/10 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </header>

                <!-- Content -->
                <div class="px-6 py-6">
                    <div class="space-y-6">
                        <!-- File Upload -->
                        <div>
                            <label for="csvFile" class="block text-sm font-medium text-white/90 mb-3">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                </svg>
                                Arquivo CSV
                            </label>
                            <div
                                class="border-2 border-dashed border-white/20 rounded-xl p-6 text-center hover:border-secondary/50 transition-colors">
                                <input id="csvFile" type="file" wire:model="csvFile" accept=".csv"
                                    class="hidden" />
                                <label for="csvFile" class="cursor-pointer">
                                    <svg class="mx-auto h-12 w-12 text-white/40 mb-4" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                    </svg>
                                    <div class="text-white/60">
                                        <span class="font-medium text-secondary hover:text-secondary/80">Clique para
                                            selecionar</span>
                                        <span class="text-white/60"> ou arraste e solte</span>
                                    </div>
                                    <p class="text-xs text-white/40 mt-2">CSV até 10MB</p>
                                </label>
                            </div>
                            @error('csvFile')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Expected Headers -->
                        <div class="bg-white/5 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-white mb-3">Cabeçalhos esperados no CSV:</h4>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                @foreach ($expectedHeaders as $header => $description)
                                    <div class="flex items-center space-x-2">
                                        <span
                                            class="px-2 py-1 bg-secondary/20 text-secondary rounded text-xs font-mono">{{ $header }}</span>
                                        <span class="text-white/60">{{ $description }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" wire:click="downloadSample"
                                class="mt-3 text-sm text-secondary hover:text-secondary/80 underline flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Baixar CSV de exemplo
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <footer
                    class="px-6 py-4 bg-white/5 border-t border-white/10 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                    <button wire:click="closeImportModal" type="button"
                        class="inline-flex items-center justify-center rounded-xl px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-medium border border-white/20 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-white/25">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Cancelar
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center rounded-xl px-6 py-3 bg-secondary hover:bg-secondary/90 text-primary font-semibold shadow-lg transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-secondary/25 transform hover:scale-105 disabled:opacity-50 disabled:transform-none">
                        <span wire:loading.remove wire:target="import">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                            </svg>
                            Enviar e Processar
                        </span>
                        <span wire:loading wire:target="import" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Enfileirando...
                        </span>
                    </button>
                </footer>
            </form>
        </div>
    </div>
</div>
