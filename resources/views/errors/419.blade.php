<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sessão Expirada - Mapa de Acessibilidade</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-primary min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl p-8 max-w-md text-center" role="alert" aria-live="assertive">
        <div class="mx-auto w-16 h-16 bg-secondary/20 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-primary mb-4">Sessão Expirada</h1>
        <p class="text-primary/80 mb-6 leading-relaxed">
            Sua sessão expirou por inatividade. Por favor, atualize a página e tente novamente.
        </p>
        <div class="space-y-3">
            <a href="{{ url('/') }}"
               class="inline-block w-full bg-secondary text-primary font-semibold px-6 py-3 rounded-lg hover:bg-secondary/90 transition-colors focus:outline-none focus:ring-2 focus:ring-secondary focus:ring-offset-2">
                Voltar ao início
            </a>
            <button onclick="window.location.reload()"
                    class="inline-block w-full bg-primary/10 text-primary font-semibold px-6 py-3 rounded-lg hover:bg-primary/20 transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                Atualizar página
            </button>
        </div>
    </div>
</body>
</html>
