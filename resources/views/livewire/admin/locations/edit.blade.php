<div class="bg-secondary p-6">
    <h3 class="text-lg font-semibold text-accent-dark mb-4">Editar Local</h3>

    <form wire:submit.prevent="save" class="space-y-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-accent-dark">Nome</label>
                <input wire:model="form.name" id="name" type="text"
                       @class(['mt-1 block w-full rounded-lg px-3 py-2 focus:outline-none focus:ring-4 sm:text-sm',
                               'border border-red-500' => $errors->has('form.name'),
                               'border border-primary focus:ring-primary/25' => !$errors->has('form.name')]) />
                @error('form.name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-accent-dark">Tipo</label>
                <select wire:model="form.type" id="type"
                        @class(['mt-1 block w-full rounded-lg px-3 py-2 focus:outline-none focus:ring-4 sm:text-sm',
                                'border border-red-500' => $errors->has('form.type'),
                                'border border-primary focus:ring-primary/25' => !$errors->has('form.type')])>
                    <option value="">Selecione um tipo</option>
                    @foreach($locationTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('form.type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="flex items-center">
                    <input wire:model="form.published" type="checkbox" class="rounded border-primary text-primary focus:ring-primary" />
                    <span class="ml-2 text-sm text-accent-dark">Publicado</span>
                </label>
            </div>

            <div class="sm:col-span-2">
                <label for="address" class="block text-sm font-medium text-accent-dark">Endereço</label>
                <textarea wire:model="form.address" id="address" rows="2"
                          @class(['mt-1 block w-full rounded-lg px-3 py-2 focus:outline-none focus:ring-4 sm:text-sm',
                                  'border border-red-500' => $errors->has('form.address'),
                                  'border border-primary focus:ring-primary/25' => !$errors->has('form.address')])></textarea>
                @error('form.address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="latitude" class="block text-sm font-medium text-accent-dark">Latitude</label>
                <input wire:model="form.latitude" id="latitude" type="number" step="any"
                       @class(['mt-1 block w-full rounded-lg px-3 py-2 focus:outline-none focus:ring-4 sm:text-sm',
                               'border border-red-500' => $errors->has('form.latitude'),
                               'border border-primary focus:ring-primary/25' => !$errors->has('form.latitude')]) />
                @error('form.latitude')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="longitude" class="block text-sm font-medium text-accent-dark">Longitude</label>
                <input wire:model="form.longitude" id="longitude" type="number" step="any"
                       @class(['mt-1 block w-full rounded-lg px-3 py-2 focus:outline-none focus:ring-4 sm:text-sm',
                               'border border-red-500' => $errors->has('form.longitude'),
                               'border border-primary focus:ring-primary/25' => !$errors->has('form.longitude')]) />
                @error('form.longitude')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="block text-sm font-medium text-accent-dark">Descrição</label>
                <textarea wire:model="form.description" id="description" rows="3"
                          @class(['mt-1 block w-full rounded-lg px-3 py-2 focus:outline-none focus:ring-4 sm:text-sm',
                                  'border border-red-500' => $errors->has('form.description'),
                                  'border border-primary focus:ring-primary/25' => !$errors->has('form.description')])></textarea>
                @error('form.description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="photos" class="block text-sm font-medium text-accent-dark">Imagens</label>
                <input wire:model="photos" id="photos" type="file" multiple accept="image/*"
                       class="mt-1 block w-full text-sm text-black file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-secondary file:text-primary hover:file:opacity-90" />
                @error('photos.*')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-4 flex justify-end gap-3">
            <button type="submit" class="inline-flex justify-center rounded-lg px-4 py-2 bg-primary text-secondary font-semibold focus:outline-none focus:ring-4 focus:ring-black/20">Salvar</button>
        </div>
    </form>
</div>


