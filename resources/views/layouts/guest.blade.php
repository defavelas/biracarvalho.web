<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Administração - Programa Bira Carvalho' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body>
    <main class="bg-gradient-to-br from-primary to-primary/80 min-h-screen">
        {{ $slot }}
    </main>

    @livewireScripts
</body>

</html>
