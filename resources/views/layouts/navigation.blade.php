<nav x-data="{ open: false }" class="bg-base-100 border-b border-base-200">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-primary" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @php($u = Auth::user())
                    @if($u && $u->role!== 'livreur')
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>


                    <x-nav-link :href="route('auth.select-cash')" :active="request()->routeIs('auth.select-cash')">
                        {{ __('Choisir la caisse') }}
                    </x-nav-link>
                    
                    @endif

                    
                    @if($u && $u->currentCash)
                        <span class="badge badge-outline">
                            {{ __('Caisse') }}: {{ $u->currentCash->name }}
                        </span>
                    @endif

                    @if($u && $u->role==='admin')
                        <x-nav-link :href="route('companies.index')" :active="request()->routeIs('companies.*')">
                            {{ __('Sociétés') }}
                        </x-nav-link>
                        <x-nav-link :href="route('livreurs.index')" :active="request()->routeIs('livreurs.*')">
                            {{ __('Livreurs') }}
                        </x-nav-link>
                        <x-nav-link :href="route('cash-registers.index')" :active="request()->routeIs('cash-registers.*')">
                            {{ __('Caisses') }}
                        </x-nav-link>
                         <x-nav-link :href="route('movements.index')" :active="request()->routeIs('movements.index')">
                        {{ __('Mouvements') }}
                    </x-nav-link>
                    @endif

                    @if($u && $u->role==='responsable_caisse')
                        <x-nav-link :href="route('cash-registers.index')" :active="request()->routeIs('cash-registers.*')">
                            {{ __('Caisses') }}
                        </x-nav-link>
                    @endif

                    @if($u && $u->role==='caissier')
                        <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                            {{ __('Clients') }}
                        </x-nav-link>
                        <x-nav-link :href="route('contre-bons.index')" :active="request()->routeIs('contre-bons.*')">
                            {{ __('Recouvrement') }}
                        </x-nav-link>
                        <x-nav-link :href="route('cheques.index')" :active="request()->routeIs('cheques.*')">
                            {{ __('Chèques') }}
                        </x-nav-link>
                         
                        <x-nav-link :href="route('movements.index')" :active="request()->routeIs('movements.index')">
                        {{ __('Mouvements') }}
                    </x-nav-link>
                       {{--  <x-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')">
                            {{ __('Dépenses') }}
                        </x-nav-link> --}}
                        {{-- <x-nav-link :href="route('transfers.index')" :active="request()->routeIs('transfers.*')">
                            {{ __('Transferts') }}
                        </x-nav-link> --}}
                    @endif

                    @if($u && $u->role==='livreur')
                        <x-nav-link :href="route('livreurs.recouvrements')" :active="request()->routeIs('livreurs.recouvrements')">
                            {{ __('Mes recouvrements') }}
                        </x-nav-link>
                    @endif

                   
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-base-content/70 bg-base-100 hover:bg-primary/5 hover:text-base-content focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('auth.select-cash')" :active="request()->routeIs('auth.select-cash')">
                    {{ __('Choisir la caisse') }}
                </x-responsive-nav-link>

                @php($u = Auth::user())
                @if($u && $u->currentCash)
                    <div class="px-4 py-2 text-xs text-gray-700 dark:text-gray-300">
                        {{ __('Caisse') }}: {{ $u->currentCash->name }}
                    </div>
                @endif

                @if($u && $u->role==='admin')
                    <x-responsive-nav-link :href="route('companies.index')" :active="request()->routeIs('companies.*')">
                        {{ __('Sociétés') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('livreurs.index')" :active="request()->routeIs('livreurs.*')">
                        {{ __('Livreurs') }}
                    </x-responsive-nav-link>
                @endif

                @if($u && $u->role==='responsable_caisse')
                    <x-responsive-nav-link :href="route('cash-registers.index')" :active="request()->routeIs('cash-registers.*')">
                        {{ __('Caisses') }}
                    </x-responsive-nav-link>
                @endif

                @if($u && $u->role==='caissier')
                    <x-responsive-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                        {{ __('Clients') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('bons.index')" :active="request()->routeIs('bons.*')">
                        {{ __('Bons') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('cheques.index')" :active="request()->routeIs('cheques.*')">
                        {{ __('Chèques') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')">
                        {{ __('Dépenses') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('transfers.index')" :active="request()->routeIs('transfers.*')">
                        {{ __('Transferts') }}
                    </x-responsive-nav-link>
                @endif

                @if($u && $u->role==='livreur')
                    <x-responsive-nav-link :href="route('livreurs.recouvrements')" :active="request()->routeIs('livreurs.recouvrements')">
                        {{ __('Mes recouvrements') }}
                    </x-responsive-nav-link>
                @endif

                <x-responsive-nav-link :href="route('movements.index')" :active="request()->routeIs('movements.index')">
                    {{ __('Mouvements') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
