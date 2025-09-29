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
        <div class="min-h-screen flex">
            
            <!-- Partie gauche : dégradé -->
            <div class="hidden lg:flex w-1/2 items-center justify-center 
                        bg-gradient-to-br from-green-600 via-emerald-500 to-teal-400 text-white p-10">
                <div class="max-w-md text-center">
                    <x-application-logo class="w-20 h-20 mx-auto mb-6 text-white" />
                    <h1 class="text-4xl font-bold mb-4">Bienvenue 👋</h1>
                    <p class="text-lg text-green-100">
                        Gérez facilement vos encaissements, paiements et mouvements de caisse
                        depuis une interface simple et moderne.
                    </p>
                </div>
            </div>

            <!-- Partie droite : contenu -->
            <div class="flex w-full lg:w-1/2 items-center justify-center bg-gray-50">
                <div class="w-full max-w-md">
                    
                    <!-- Logo mobile -->
                    <div class="flex justify-center mb-6 lg:hidden">
                        <a href="/">
                            <x-application-logo class="w-16 h-16 text-green-600" />
                        </a>
                    </div>

                    <!-- Slot pour contenu (login, register, etc.) -->
                    <div class="bg-white shadow-xl rounded-2xl px-8 py-10">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
