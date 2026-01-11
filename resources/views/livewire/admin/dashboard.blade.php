<div>
    @if (session('message'))
        <div class="mb-4 p-4 bg-green-500/10 border border-green-500/20 rounded-lg">
            <p class="text-green-200">{{ session('message') }}</p>
        </div>
    @endif

    <header class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">{{ __('Dashboard') }}</h1>
        <p class="text-white/80">{{ __('Gerencie os registros pendentes de aprovação') }}</p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-white/70">{{ __('Total de Registros') }}</h3>
                    <p class="text-3xl font-bold text-white mt-2">{{ number_format($totalLocations) }}</p>
                    @if($lastUpdatedAt)
                        <p class="text-xs text-white/50 mt-1">{{ __('Última atualização:') }} {{ $lastUpdatedAt }} BRT</p>
                    @endif
                </div>
                <div class="p-3 bg-blue-500/20 rounded-full">
                    @svg('heroicon-o-map-pin', 'w-6 h-6 text-blue-400')
                </div>
            </div>
        </div>

        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-white/70">{{ __('Pendentes') }}</h3>
                    <p class="text-3xl font-bold text-white mt-2">{{ number_format($totalPending) }}</p>
                    <p class="text-xs text-white/50 mt-1">
                        <span class="text-yellow-400">{{ number_format($totalPending) }}</span> de {{ number_format($totalLocations) }}
                    </p>
                </div>
                <div class="p-3 bg-yellow-500/20 rounded-full">
                    @svg('heroicon-o-clock', 'w-6 h-6 text-yellow-400')
                </div>
            </div>
        </div>

        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-white/70">{{ __('Publicados') }}</h3>
                    <p class="text-3xl font-bold text-white mt-2">{{ number_format($totalPublished) }}</p>
                    <p class="text-xs text-white/50 mt-1">
                        <span class="text-green-400">{{ number_format($totalPublished) }}</span> de {{ number_format($totalLocations) }}
                    </p>
                </div>
                <div class="p-3 bg-green-500/20 rounded-full">
                    @svg('heroicon-o-check-circle', 'w-6 h-6 text-green-400')
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-white">
                {{ __('Registros Pendentes') }} ({{ $pendingLocations->total() }})
            </h2>

            <div class="text-sm text-white/70">
                {{ __('Máximo 25 registros por página') }}
            </div>
        </div>

        @if ($pendingLocations->count() > 0)
            <div class="space-y-4">
                @foreach ($pendingLocations as $location)
                    <div class="bg-white/5 border border-white/10 rounded-lg p-6 hover:bg-white/10 transition-colors duration-200">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-medium text-white">{{ $location->name }}</h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white border border-white/30" style="background-color: {{ $location->type->color() }}">
                                        {{ $location->type->label() }}
                                    </span>
                                </div>

                                @if($location->type)
                                    <p class="text-white/80 text-sm mb-2">{{ $location->type }}</p>
                                @endif

                                <div class="flex items-center gap-4 text-sm text-white/60">
                                    <span class="flex items-center gap-1">
                                        @svg('heroicon-o-map-pin', 'w-4 h-4')
                                        {{ number_format($location->latitude, 6) }}, {{ number_format($location->longitude, 6) }}
                                    </span>

                                    @if($location->authors)
                                        <span class="flex items-center gap-1">
                                            @svg('heroicon-o-user', 'w-4 h-4')
                                            {{ $location->authors }}
                                        </span>
                                    @endif

                                    <span class="flex items-center gap-1">
                                        @svg('heroicon-o-clock', 'w-4 h-4')
                                        {{ $location->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>

                                @if($location->images->count() > 0)
                                    <div class="mt-3 flex items-center gap-2">
                                        @svg('heroicon-o-photo', 'w-4 h-4 text-white/60')
                                        <span class="text-sm text-white/60">{{ $location->images->count() }} {{ __('imagem(ns)') }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3">
                                <button
                                    wire:click="approve('{{ $location->id }}')"
                                    wire:confirm="{{ __('Tem certeza que deseja aprovar este registro?') }}"
                                    class="inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                                >
                                    @svg('heroicon-o-check', 'w-4 h-4 mr-2')
                                    {{ __('Aprovar') }}
                                </button>

                                <button
                                    wire:click="reject('{{ $location->id }}')"
                                    wire:confirm="{{ __('Tem certeza que deseja rejeitar este registro? Esta ação não pode ser desfeita.') }}"
                                    class="inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                                >
                                    @svg('heroicon-o-x-mark', 'w-4 h-4 mr-2')
                                    {{ __('Rejeitar') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $pendingLocations->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="mb-4">
                    @svg('heroicon-o-check-circle', 'w-16 h-16 mx-auto text-green-400')
                </div>
                <h3 class="text-lg font-medium text-white mb-2">{{ __('Nenhum registro pendente') }}</h3>
                <p class="text-white/60">{{ __('Todos os registros foram moderados ou não há novos registros.') }}</p>
            </div>
        @endif
    </div>
</div>
