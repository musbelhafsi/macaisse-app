<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl">
                {{ __('Dashboard caisse') }}
            </h2>
            <div>
                 <button id="themeToggle" class="btn btn-sm">
                    Theme
                </button> 
                <button
                class="btn btn-sm"
                onclick="(function(){
                    const cur = localStorage.getItem('theme') || 'enterprise';
                    const next = cur === 'enterprise' ? 'enterprise-dark' : 'enterprise';
                    localStorage.setItem('theme', next);
                    location.reload();
                })()"
                >  Basculer thème
                </button>

            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Solde et activité -->
                <div class="stats shadow col-span-1 md:col-span-2">
                    <div class="stat">
                        <div class="stat-title">Solde caisse</div>
                        <div class="stat-value">{{ $currentBalance !== null ? number_format($currentBalance, 2, ',', ' ') : '—' }}</div>
                        <div class="stat-desc">Caisse courante</div>
                    </div>
                    <div class="stat">
                        <div class="stat-title">Mouvements (24h)</div>
                        <div class="stat-value">{{ $movementsCount ?? '—' }}</div>
                        <div class="stat-desc">Dernières 24h</div>
                    </div>
                </div>

                
                <!-- Caisse courante / Raccourcis -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h3 class="card-title text-sm">Caisse courante</h3>
                        @if(Auth::user()?->currentCash)
                            <div class="flex items-center gap-2 mb-3">
                                <span class="badge badge-outline">{{ Auth::user()->currentCash->name }}</span>
                            </div>
                        @else
                            <div class="alert alert-warning mb-3">
                                Aucune caisse sélectionnée.
                                <a href="{{ route('auth.select-cash') }}" class="link">Choisir</a>
                            </div>
                        @endif
                        <h3 class="card-title text-sm">Raccourcis</h3>
                        <div class="flex flex-wrap gap-2">
                            <a class="btn btn-primary btn-sm" href="{{ route('auth.select-cash') }}">Choisir la caisse</a>
                            <a class="btn btn-outline btn-sm" href="{{ route('bons.index') }}">Bons</a>
                            <a class="btn btn-outline btn-sm" href="{{ route('cheques.index') }}">Chèques</a>
                            <a class="btn btn-outline btn-sm" href="{{ route('expenses.index') }}">Dépenses</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtres période -->
            <div class="mt-6 card bg-base-100 shadow">
                <div class="card-body">
                    <form method="get" action="{{ route('dashboard') }}" class="grid md:grid-cols-5 gap-4 items-end">
                        <div class="form-control">
                            <x-input-label value="Du" />
                            <x-text-input type="date" name="from" value="{{ $periodFrom }}" />
                        </div>
                        <div class="form-control">
                            <x-input-label value="Au" />
                            <x-text-input type="date" name="to" value="{{ $periodTo }}" />
                        </div>
                        <div class="md:col-span-3">
                            <button class="btn btn-primary">Appliquer</button>
                            <a class="btn btn-ghost" href="{{ route('dashboard') }}">Réinitialiser</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistiques chèques & clients recouvrés (période) -->
            <div class="mt-6 stats shadow">
                <div class="stat">
                    <div class="stat-title">Chèques (nombre)</div>
                    <div class="stat-value">{{ $chequesCount ?? 0 }}</div>
                </div>
                <div class="stat">
                    <div class="stat-title">Chèques (total)</div>
                    <div class="stat-value">{{ number_format($chequesSum ?? 0, 2, ',', ' ') }}</div>
                </div>
                <div class="stat">
                    <div class="stat-title">Clients recouvrés</div>
                    <div class="stat-value">{{ $clientsRecouvres ?? 0 }}</div>
                </div>
            </div>
            <!-- KPI période -->
                <div class="mt-6 stats shadow">
                    <div class="stat">
                        <div class="stat-title">Entrées ({{ $periodFrom }} → {{ $periodTo }})</div>
                        <div class="stat-value text-success">{{ number_format($totalsIn ?? 0, 2, ',', ' ') }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-title">Sorties</div>
                        <div class="stat-value text-error">{{ number_format($totalsOut ?? 0, 2, ',', ' ') }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-title">Flux net</div>
                        <div class="stat-value {{ ($netFlow ?? 0) >= 0 ? 'text-success' : 'text-error' }}">{{ number_format($netFlow ?? 0, 2, ',', ' ') }}</div>
                    </div>
                </div>
                <!-- END KPI période -->

         <!-- Graphique: flux par jour (période) -->
<div class="mt-6 card bg-base-100 shadow">
    <div class="card-body">
        <h3 class="card-title">Flux quotidiens (entrées vs sorties)</h3>
        <canvas id="fluxChart" height="220"></canvas>

        
    </div>
</div>



            <!-- Répartition par type -->
            <div class="mt-6 card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="card-title">Répartition par type (période)</h3>
                    @if(($byType ?? collect())->count())
                        <div class="overflow-x-auto">
                            <table class="table table-zebra">
                                <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Nombre</th>
                                    <th>Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($byType as $row)
                                    <tr>
                                        <td>{{ $row->type }}</td>
                                        <td>{{ $row->cnt }}</td>
                                        <td>{{ number_format($row->total, 2, ',', ' ') }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-sm text-base-content/70">Aucune donnée pour la période</div>
                    @endif
                </div>
            </div>

            <!-- Derniers mouvements -->
            <div class="mt-6 card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="card-title">Derniers mouvements</h3>
                  {{--   @if(($lastMovements ?? collect())->count()) --}}
                @if(collect($lastMovements ?? [])->count())
                        <div class="overflow-x-auto">
                            <table class="table table-zebra">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th class="text-right">Montant</th>
                                        <th>Source</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($lastMovements as $m)
                                    <tr>
                                        <td>{{ $m->date_mvt }}</td>
                                        <td>{{ $m->type }}</td>
                                        <td class="text-right">{{ number_format($m->montant, 2, ',', ' ') }}</td>
                                        <td>{{ class_basename($m->source_type) }}#{{ $m->source_id }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-sm text-base-content/70">Aucun mouvement</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle entre les thèmes enterprise / enterprise-dark (sans rechargement)
        document.getElementById('themeToggle')?.addEventListener('click', () => {
            const root = document.documentElement;
            const current = localStorage.getItem('theme') || root.getAttribute('data-theme') || 'enterprise';
            const next = current === 'enterprise' ? 'enterprise-dark' : 'enterprise';
            root.setAttribute('data-theme', next);
            if (next.includes('dark')) root.classList.add('dark'); else root.classList.remove('dark');
            localStorage.setItem('theme', next);
        });
    </script>
</x-app-layout>
