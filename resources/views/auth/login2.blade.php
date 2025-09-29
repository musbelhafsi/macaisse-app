<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-900">
        <div class="w-full max-w-md bg-gray-900 shadow-lg rounded-2xl p-8 text-gray-100">
            
            <!-- Titre -->
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold">Gestion de Caisse</h1>
                <p class="text-gray-400">Connectez-vous à votre compte</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div>
                    <x-input-label for="email" :value="__('Email')" class="text-gray-200" />
                    <x-text-input id="email" 
                        class="block mt-1 w-full bg-gray-700 border-gray-600 text-gray-100 focus:ring-blue-500" 
                        type="email" name="email" :value="old('email')" required autofocus />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Mot de passe')" class="text-gray-200" />
                    <x-text-input id="password" 
                        class="block mt-1 w-full bg-gray-700 border-gray-600 text-gray-100 focus:ring-blue-500"
                        type="password" name="password" required />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
                </div>

                <!-- Remember Me -->
                <div class="block mt-4 flex items-center justify-between">
                    <label for="remember_me" class="flex items-center">
                        <input id="remember_me" type="checkbox" 
                            class="rounded border-gray-500 text-blue-400 bg-gray-700 focus:ring-blue-500" 
                            name="remember">
                        <span class="ms-2 text-sm text-gray-300">Se souvenir de moi</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-sm text-blue-400 hover:underline" 
                           href="{{ route('password.request') }}">
                            Mot de passe oublié ?
                        </a>
                    @endif
                </div>

                <!-- Button -->
                <div class="mt-6">
                   <x-primary-button class="w-full justify-center bg-green-600 hover:bg-green-700">
                          Se connecter
                    </x-primary-button>


                </div>
            </form>

            <!-- Register link -->
            <p class="text-center text-gray-400 text-sm mt-6">
                Pas encore inscrit ?
                <a href="{{ route('register') }}" class="text-blue-400 hover:underline">
                    Créer un compte
                </a>
            </p>
        </div>
    </div>
</x-guest-layout>
