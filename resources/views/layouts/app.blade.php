<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <script>
            // Thème entreprise par défaut (clair), avec variante sombre "enterprise-dark"
            // On garde la compatibilité si un ancien stockage utilise 'light'/'dark'.
            (function(){
                const key = 'theme';
                const saved = localStorage.getItem(key);
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                // Map legacy values
                const mapLegacy = (v) => v === 'dark' ? 'enterprise-dark' : (v === 'light' ? 'enterprise' : v);
                let theme = mapLegacy(saved || (prefersDark ? 'enterprise-dark' : 'enterprise'));

                // Si la valeur n'est pas reconnue, fallback
                if (!['enterprise','enterprise-dark','light','dark'].includes(theme)) theme = 'enterprise';

                document.documentElement.setAttribute('data-theme', theme);
                // Classe Tailwind 'dark' uniquement pour la variante sombre
                if (theme === 'enterprise-dark' || theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            })();
        </script>

        <div class="min-h-screen bg-base-200">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-base-100 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="p-4">
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </main>
        </div>
    </body>
</html>
