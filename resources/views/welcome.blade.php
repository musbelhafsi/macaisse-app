<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion de Caisse</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="text-center p-8 bg-white shadow-xl rounded-2xl w-full max-w-lg">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Bienvenue sur Gestion de Caisse</h1>
        <p class="text-gray-600 mb-6">Une application simple pour gérer vos encaissements, dépenses et rapports quotidiens.</p>

        <div class="flex justify-center gap-4">
            @auth
                <a href="{{ route('dashboard') }}" 
                   class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                   Accéder au tableau de bord
                </a>
            @else
            <div class="mt-6">   
            <a href="{{ route('login') }}" 
                   class="px-6 py-2 bg-blue-600 text-gray-800 rounded-lg hover:bg-blue-700">
                   Connexion
                </a> 
            </div>
               <div class="mt-6"> 
               <a href="{{ route('register') }}" 
                   class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                   S’inscrire
                </a>
               </div>
            @endauth
        </div>
    </div>
</body>
</html>
