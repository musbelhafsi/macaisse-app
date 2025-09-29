<x-guest-layout>
    <div class="min-h-screen flex">
        
       {{--  <!-- Partie gauche avec image -->
        <div class="hidden lg:flex w-1/2 bg-cover bg-center" 
             style="background-image: url('https://source.unsplash.com/800x1000/?finance,money,office');">
        </div> --}}

        <!-- Partie droite avec formulaire -->
        {{-- <div class="flex w-full lg:w-1/2 items-center justify-center bg-gray-50"> --}}
            <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-10">
                
                <!-- Titre -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Gestion de Caisse</h1>
                    <p class="text-gray-500 mt-2">Connectez-vous à votre compte</p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email"
                            class="block mt-1 w-full border-gray-300 rounded-lg focus:border-green-500 focus:ring-green-500"
                            type="email" name="email" :value="old('email')" required autofocus />
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500" />
                    </div>

                    <!-- Password -->
                    <div class="mt-4">
                        <x-input-label for="password" :value="__('Mot de passe')" />
                        <x-text-input id="password"
                            class="block mt-1 w-full border-gray-300 rounded-lg focus:border-green-500 focus:ring-green-500"
                            type="password" name="password" required />
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500" />
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between mt-4">
                        <label for="remember_me" class="flex items-center">
                            <input id="remember_me" type="checkbox"
                                class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                name="remember">
                            <span class="ml-2 text-sm text-gray-600">Se souvenir de moi</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm text-green-600 hover:underline"
                               href="{{ route('password.request') }}">
                                Mot de passe oublié ?
                            </a>
                        @endif
                    </div>

                    <!-- Bouton -->
                    <div class="mt-6">
                        <x-primary-button class="w-full justify-center bg-green-600 hover:bg-green-700 text-white">
                            Se connecter
                        </x-primary-button>
                    </div>
                </form>

                <!-- Lien inscription -->
                <p class="text-center text-gray-500 text-sm mt-6">
                    Pas encore inscrit ?
                    <a href="{{ route('register') }}" class="text-green-600 hover:underline">
                        Créer un compte
                    </a>
                </p>
            {{-- </div> --}}
    </div>
</x-guest-layout>
