<div class="flex max-w-screen-xl mx-auto min-h-screen">
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 flex-col justify-center px-12 xl:px-16">
        <div class="max-w-md xl:max-w-lg text-white">
            <header class="mb-8">
                <h1 class="text-4xl xl:text-5xl font-bold mb-4 leading-tight">
                    Bem-vindo ao<br>
                    <span class="text-secondary">Programa Bira Carvalho</span>
                </h1>
                <p class="text-lg xl:text-xl text-white/90 leading-relaxed">
                    Território, Acessibilidade e Tecnologia na Maré
                </p>
            </header>

            <section class="space-y-4 text-white/80">
                <p class="text-base xl:text-lg leading-relaxed">
                    Uma aplicação colaborativa para mapeamento digital das condições de acessibilidade
                    urbana para pessoas com deficiência na Favela da Maré.
                </p>

                <div class="space-y-2">
                    <div class="flex items-start space-x-2">
                        <div class="w-2 h-2 bg-secondary rounded-full mt-2.5 flex-shrink-0"></div>
                        <p class="text-sm xl:text-base">
                            <strong class="text-secondary">Mapeamento Colaborativo:</strong>
                            Engajamento da comunidade no processo de documentação das barreiras urbanas
                        </p>
                    </div>

                    <div class="flex items-start space-x-2">
                        <div class="w-2 h-2 bg-secondary rounded-full mt-2.5 flex-shrink-0"></div>
                        <p class="text-sm xl:text-base">
                            <strong class="text-secondary">Tecnologia Inclusiva:</strong>
                            Interface web acessível seguindo padrões WCAG e WAI-ARIA
                        </p>
                    </div>

                    <div class="flex items-start space-x-2">
                        <div class="w-2 h-2 bg-secondary rounded-full mt-2.5 flex-shrink-0"></div>
                        <p class="text-sm xl:text-base">
                            <strong class="text-secondary">Impacto Social:</strong>
                            Desenvolvimento de políticas públicas mais inclusivas para favelas e periferias
                        </p>
                    </div>
                </div>
            </section>
            <footer class="mt-8 text-white text-xs font-mono">
                &copy;Bira Carvalho, 2025. Tecnologia <span class="text-secondary">BSON Labs</span>.
            </footer>
        </div>
    </div>

    <div class="flex-1 lg:w-1/2 xl:w-2/5 flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-md">
            <div class="lg:hidden text-center mb-4">
                <h1 class="text-3xl font-bold text-white mb-2">
                    Programa <span class="text-secondary">Bira Carvalho</span>
                </h1>
                <p class="text-white/80">
                    Acesso à Administração
                </p>
            </div>

            <div class="bg-white rounded-2xl shadow-2xl p-8">
                <header class="mb-8">
                    <h2 id="login-form" class="text-2xl font-bold text-primary mb-2">
                        Área administrativa
                    </h2>
                    <p class="text-accent-dark/70 text-sm">
                        Este acesso é restrito ao pessoal autorizado, por favor, entre com seu usuário e senha.
                    </p>
                </header>

                <form wire:submit="login" class="space-y-4" novalidate>
                    <div>
                        <label for="username" class="block text-sm font-medium text-accent-dark mb-2">
                            Usuário
                        </label>
                        <input wire:model="form.username" id="username" name="username" type="text" required
                            aria-describedby="username-error" @class([
                                'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-4 transition-colors duration-200',
                                'border-red-500 focus:ring-red-500/25 focus:border-red-500' => $errors->has(
                                    'form.username'),
                                'border-gray-300 focus:ring-primary/25 focus:border-primary' => !$errors->has(
                                    'form.username'),
                            ])
                            placeholder="Digite seu usuário">
                        @error('form.username')
                            <p id="username-error" class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-accent-dark mb-2">
                            Senha de acesso
                        </label>
                        <input wire:model="form.password" id="password" name="password" type="password" required
                            aria-describedby="password-error" @class([
                                'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-4 transition-colors duration-200',
                                'border-red-500 focus:ring-red-500/25 focus:border-red-500' => $errors->has(
                                    'form.password'),
                                'border-gray-300 focus:ring-primary/25 focus:border-primary' => !$errors->has(
                                    'form.password'),
                            ])
                            placeholder="Digite sua senha">
                        @error('form.password')
                            <p id="password-error" class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full bg-primary hover:bg-primary/90 focus:bg-primary/90 text-white font-semibold py-3 px-4 rounded-lg focus:outline-none focus:ring-4 focus:ring-primary/25 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove>
                            Entrar
                        </span>
                        <span wire:loading class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span>
                                Aguarde...
                            </span>
                        </span>
                    </button>
                </form>

                <footer class="mt-6 text-center">
                    <p class="text-xs text-accent-dark/60">
                        Sistema de administração do Programa Bira Carvalho<br />
                        <strong>Território, Acessibilidade e Tecnologia na Maré</strong>
                    </p>
                </footer>
            </div>
        </div>
    </div>
</div>
