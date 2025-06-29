<x-modal :show="$showModal" max-width="md" wire="showModal" wire:click.self="hideModal">
    <div class="p-6">
        <header class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                @svg('heroicon-o-user', 'w-5 h-5 text-primary')
                <h2 id="modal-title" class="text-xl font-semibold text-primary">
                    Administrar
                </h2>
            </div>
            <button 
                type="button" 
                wire:click="hideModal"
                class="text-white/50 hover:text-white focus:outline-none focus:ring-2 focus:ring-secondary cursor-pointer"
                aria-label="Fechar modal"
            >
                @svg('heroicon-o-x-mark', 'w-6 h-6 text-primary')
            </button>
        </header>

        <form wire:submit="login" class="space-y-4">
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-primary mb-1.5">
                    Nome de usuário
                </label>
                <input 
                    type="text" 
                    id="username" 
                    wire:model="username"
                    placeholder="Digite seu nome de usuário"
                    class="bg-white w-full px-4 py-3 text-sm border border-primary rounded-lg focus:outline-none focus:ring-4 focus:ring-primary/25 focus:border-primary transition-all duration-200"
                    aria-describedby="username-error"
                >
                @error('username')
                    <p id="username-error" class="text-sm text-rose-400" role="alert">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-primary mb-1.5">
                    Senha de acesso
                </label>
                <input 
                    type="password" 
                    id="password" 
                    wire:model="password"
                    placeholder="Digite sua senha"
                    class="bg-white w-full px-4 py-3 text-sm border border-primary rounded-lg focus:outline-none focus:ring-4 focus:ring-primary/25 focus:border-primary transition-all duration-200"
                    aria-describedby="password-error"
                >
                @error('password')
                    <p id="password-error" class="text-sm text-rose-400" role="alert">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="flex items-center">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input 
                        type="checkbox" 
                        wire:model="remember"
                        class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary focus:ring-2 accent-primary"
                    >
                    <span class="text-sm text-primary">Lembrar do meu acesso?</span>
                </label>
            </div>

            <button 
                type="submit"
                class="w-full bg-primary text-secondary font-semibold py-3 px-4 rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-4 focus:ring-black/25 transition-all duration-200 text-base cursor-pointer"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50 cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="login">Entrar</span>
                <span wire:loading wire:target="login" class="flex items-center justify-center space-x-2">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Entrando...
                </span>
            </button>
        </form>
    </div>
</x-modal> 