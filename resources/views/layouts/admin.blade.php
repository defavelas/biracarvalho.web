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
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center gap-2 text-white hover:text-secondary text-base font-medium transition-colors duration-200 {{ request()->routeIs('admin.dashboard') ? 'text-secondary' : '' }}">
                            @svg('heroicon-o-squares-2x2', 'w-6 h-6')
                            <span class="hidden sm:inline">Dashboard</span>
                        </a>
                        
                        <a href="{{ route('admin.locations.records') }}" 
                           class="flex items-center gap-2 text-white hover:text-secondary text-base font-medium transition-colors duration-200 {{ request()->routeIs('admin.locations.*') ? 'text-secondary' : '' }}">
                            @svg('heroicon-o-map-pin', 'w-6 h-6')
                            <span class="hidden sm:inline">Locais</span>
                        </a>
                        
                        <button onclick="syncFromKobo()" 
                                class="flex items-center gap-2 text-white hover:text-secondary text-base font-medium transition-colors duration-200">
                            @svg('heroicon-o-arrow-path', 'w-6 h-6 sync-icon')
                            <span class="hidden sm:inline">Sincronizar</span>
                        </button>

                        <div class="flex items-center gap-2 text-white/80">
                            <span class="text-sm">{{ auth()->user()->name }}</span>
                        </div>

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
    
    <script>
        function syncFromKobo() {
            const syncIcon = document.querySelector('.sync-icon');
            const syncButton = syncIcon.closest('button');
            
            // Add loading state
            syncIcon.classList.add('animate-spin');
            syncButton.disabled = true;
            syncButton.classList.add('opacity-50');
            
            fetch('/admin/sync-kobo', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success notification
                    showNotification('Sincronização iniciada com sucesso!', 'success');
                    // Optionally refresh the page after a delay
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    showNotification(data.message || 'Erro na sincronização', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Erro de conexão', 'error');
            })
            .finally(() => {
                // Remove loading state
                syncIcon.classList.remove('animate-spin');
                syncButton.disabled = false;
                syncButton.classList.remove('opacity-50');
            });
        }
        
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${
                type === 'success' ? 'bg-green-600' : 
                type === 'error' ? 'bg-red-600' : 
                'bg-blue-600'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
    </script>
</body>
</html>

