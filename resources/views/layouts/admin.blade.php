<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Administração - Programa Bira Carvalho' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gradient-to-br from-primary to-primary/90 min-h-screen text-white">
    @auth
        <nav class="py-4">
            <div class="max-w-screen-xl mx-auto px-4 md:px-0">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <img src="{{ asset('assets/images/logo.svg') }}" alt="Bira Carvalho" class="w-16">
                    </div>
                    
                    <div class="flex items-center gap-x-4">
                        <a href="{{ route('admin.locations.records') }}" 
                           class="text-white hover:text-secondary text-base font-medium transition-colors duration-200 {{ request()->routeIs('admin.locations.*') ? 'text-secondary' : '' }}">
                            @svg('heroicon-o-map-pin', 'w-6 h-6')
                        </a>
                        
                        <a href="{{ route('admin.csv-import') }}" 
                           class="text-white hover:text-secondary text-base font-medium transition-colors duration-200 {{ request()->routeIs('admin.csv-import') ? 'text-secondary' : '' }}">
                            @svg('heroicon-o-arrow-up-tray', 'w-6 h-6')
                        </a>

                        <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-secondary hover:bg-secondary/90 text-primary px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200 cursor-pointer">
                                @svg('heroicon-o-arrow-right-on-rectangle', 'w-5 h-5')
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
    @endauth

    <main id="main-content" class="max-w-screen-xl mx-auto px-4 md:px-0">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>

