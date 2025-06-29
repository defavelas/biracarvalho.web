<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Programa Bira Carvalho - Maré' }}</title>

        <!-- Google Fonts - Libre Franklin -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@400;600;700&display=swap" rel="stylesheet">

        <!-- Open Graph -->
        <meta property="og:title" content="{{ $title ?? 'Programa Bira Carvalho (Maré)' }}">
        <meta property="og:description" content="O Programa Bira Carvalho é um projeto de inclusão social que visa oferecer acesso aos espaços públicos para pessoas com deficiência. Ele é um esforço colaborativo entre a Prefeitura do Rio de Janeiro e a Bira Carvalho, uma empresa de tecnologia que desenvolveu uma plataforma de mapas interativos para facilitar a localização de espaços acessíveis.">
        <meta property="og:image" content="{{ asset('assets/images/og-image.jpg') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:type" content="website">
        <meta property="og:locale" content="pt-BR">

        <!-- Favicon -->
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicon/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/favicon/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/favicon/favicon-16x16.png') }}">
        <link rel="icon" href="{{ asset('assets/favicon/favicon.ico') }}">
        <link rel="manifest" href="{{ asset('assets/favicon/site.webmanifest') }}">
        <meta name="theme-color" content="#653089">
        
        <!-- Leaflet CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
            crossorigin=""/>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans bg-gray-50">
        {{ $slot }}
        
        <!-- Leaflet JS -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
        
        @stack('scripts')
    </body>
</html>
