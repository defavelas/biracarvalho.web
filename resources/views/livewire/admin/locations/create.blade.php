<div class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto" x-data="{ show: @entangle('showModal') }" x-show="show"
    x-cloak>
    <!-- Backdrop -->
    <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/80 backdrop-blur-sm" wire:click="closeModal"></div>

    <!-- Modal -->
    <div x-show="show" x-transition:enter="ease-out duration-400 delay-150"
        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-8 scale-95"
        class="relative w-full max-w-2xl mx-4 bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl shadow-2xl overflow-hidden"
        x-trap.noscroll="show">
        <form wire:submit.prevent="save">
            <!-- Header -->
            <header class="px-6 py-4 border-b border-white/10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-secondary/20 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white">Novo Local</h3>
                            <p class="text-sm text-white/70">Adicionar um novo local de acessibilidade</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" type="button"
                        class="text-white/60 hover:text-white p-2 rounded-lg hover:bg-white/10 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="px-6 py-6 max-h-96 overflow-y-auto">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-input wire:model="form.name" label="Nome do Local" id="name" :required="true"
                            :error="$errors->first('form.name')" placeholder="Ex: Praça da Acessibilidade"
                            icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a1.994 1.994 0 01-1.414.586H7a4 4 0 01-4-4V7a4 4 0 014-4z'/>" />
                    </div>

                    <div>
                        @php
                            $typeOptions = collect($locationTypes)
                                ->map(fn($label, $value) => ['value' => $value, 'label' => $label])
                                ->values()
                                ->toArray();
                        @endphp
                        <x-select wire:model="form.type" label="Tipo de Acessibilidade" :required="true"
                            :error="$errors->first('form.type')" :options="$typeOptions" placeholder="Selecione o tipo de acessibilidade"
                            icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'/>" />
                    </div>

                    <div class="flex items-end pb-2">
                        <label
                            class="flex items-center space-x-3 cursor-pointer p-3 rounded-lg hover:bg-white/5 transition-colors">
                            <input wire:model="form.published" type="checkbox"
                                class="w-5 h-5 rounded border-white/20 bg-white/10 text-secondary focus:ring-secondary/25 focus:ring-offset-0" />
                            <div>
                                <span class="text-sm font-medium text-white">Publicar Local</span>
                                <p class="text-xs text-white/60">Tornar visível no mapa público</p>
                            </div>
                        </label>
                    </div>

                    <div class="sm:col-span-2">
                        <x-input wire:model="form.address" type="textarea" label="Endereço Completo" :required="true"
                            :error="$errors->first('form.address')" placeholder="Rua, número, bairro, cidade..." rows="3"
                            icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z'/><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 11a3 3 0 11-6 0 3 3 0 016 0z'/>" />
                    </div>

                    <div>
                        <x-input wire:model="form.latitude" type="number" step="any" label="Latitude"
                            :required="true" :error="$errors->first('form.latitude')" placeholder="-22.906847"
                            help="Coordenada de localização no mapa"
                            icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7'/>" />
                    </div>

                    <div>
                        <x-input wire:model="form.longitude" type="number" step="any" label="Longitude"
                            :required="true" :error="$errors->first('form.longitude')" placeholder="-43.172896"
                            help="Coordenada de localização no mapa"
                            icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7'/>" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input wire:model="form.description" type="textarea" label="Descrição Detalhada"
                            :error="$errors->first('form.description')"
                            placeholder="Descreva as características de acessibilidade, barreiras encontradas..."
                            rows="4"
                            icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'/>" />
                    </div>

                    <div class="sm:col-span-2">
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-white/90">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Imagens do Local
                            </label>
                            
                            <!-- Hidden file input -->
                            <input wire:model="photos" type="file" multiple accept="image/*" class="hidden" id="photo-upload" />
                            
                            <!-- Image slots grid -->
                            <div class="grid grid-cols-5 gap-3">
                                @for($i = 0; $i < 5; $i++)
                                    <div class="aspect-square">
                                        @if(isset($photos[$i]))
                                            <!-- Image preview -->
                                            <div class="relative w-full h-full rounded-lg overflow-hidden border-2 border-white/20 bg-white/5">
                                                <img src="{{ $photos[$i]->temporaryUrl() }}" 
                                                     alt="Imagem {{ $i + 1 }} do local"
                                                     class="w-full h-full object-cover" />
                                                <button type="button" 
                                                        wire:click="removePhoto({{ $i }})"
                                                        class="absolute top-1 right-1 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center transition-colors"
                                                        aria-label="Remover imagem {{ $i + 1 }}">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        @else
                                            <!-- Empty slot with upload trigger -->
                                            <button type="button" 
                                                    onclick="document.getElementById('photo-upload').click()"
                                                    class="w-full h-full rounded-lg border-2 border-dashed border-white/30 bg-white/5 hover:bg-white/10 hover:border-white/50 transition-all duration-200 flex flex-col items-center justify-center group focus:outline-none focus:ring-4 focus:ring-secondary/25"
                                                    aria-label="Adicionar imagem {{ $i + 1 }}">
                                                <svg class="w-6 h-6 text-white/60 group-hover:text-white/80 transition-colors" 
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span class="text-xs text-white/50 group-hover:text-white/70 mt-1 transition-colors">
                                                    {{ $i === 0 ? 'Adicionar' : '+' }}
                                                </span>
                                            </button>
                                        @endif
                                    </div>
                                @endfor
                            </div>
                            
                            <p class="text-xs text-white/60">
                                Adicione até 5 fotos que mostrem as condições de acessibilidade do local
                            </p>
                            
                            @error('photos.*')
                                <p class="text-sm text-red-400" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer
                class="px-6 py-4 bg-white/5 border-t border-white/10 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <button wire:click="closeModal" type="button"
                    class="inline-flex items-center justify-center rounded-xl px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-medium border border-white/20 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-white/25">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cancelar
                </button>
                <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center rounded-xl px-6 py-3 bg-secondary hover:bg-secondary/90 text-primary font-semibold shadow-lg transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-secondary/25 transform hover:scale-105 disabled:opacity-50 disabled:transform-none">
                    <span wire:loading.remove>
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Salvar Local
                    </span>
                    <span wire:loading class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Salvando...
                    </span>
                </button>
            </footer>
        </form>
    </div>
</div>
