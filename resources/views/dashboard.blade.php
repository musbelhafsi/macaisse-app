@extends('layouts.app')

@section('content')
<div class="max-w-9xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <!-- En-tête avec période -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold">Tableau de bord</h1>
            <p class="text-gray-600">Période du {{ \Carbon\Carbon::parse($periodFrom)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($periodTo)->format('d/m/Y') }}</p>
        </div>
        
        <!-- Filtres période -->
        <form method="get" action="{{ route('dashboard') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex gap-2">
                <input type="date" name="from" value="{{ $periodFrom }}" class="input input-bordered input-sm">
                <span class="self-center">au</span>
                <input type="date" name="to" value="{{ $periodTo }}" class="input input-bordered input-sm">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Appliquer</button>
            <a href="{{ route('dashboard') }}" class="btn btn-outline btn-sm">Aujourd'hui</a>
        </form>
    </div>

    <!-- Alertes -->
    @if(count($alerts) > 0)
    <div class="grid grid-cols-1 gap-4 mb-6">
        @foreach($alerts as $alert)
        <div class="alert alert-{{ $alert['type'] }} shadow-lg">
            <div class="flex items-center justify-between">
                <span>{{ $alert['message'] }}</span>
                <a href="{{ $alert['link'] }}" class="btn btn-xs">Voir</a>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- KPI Principaux -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
        <!-- Solde actuel -->
        <div class="stat bg-base-100 shadow-lg rounded-lg p-6 border-l-4 border-primary">
            <div class="stat-title text-lg">Solde actuel</div>
            <div class="stat-value text-3xl {{ $currentBalance >= 0 ? 'text-success' : 'text-error' }}">
                {{ number_format($currentBalance, 2, ',', ' ') }} €
            </div>
            <div class="stat-desc">{{ $currentCash->name ?? 'Aucune caisse' }}</div>
        </div>

        <!-- Flux net période -->
        <div class="stat bg-base-100 shadow-lg rounded-lg p-6 border-l-4 border-secondary">
            <div class="stat-title text-lg">Flux net</div>
            <div class="stat-value text-3xl {{ $fluxNet >= 0 ? 'text-success' : 'text-error' }}">
                {{ number_format($fluxNet, 2, ',', ' ') }} €
            </div>
            <div class="stat-desc">{{ $periodFrom }} → {{ $periodTo }}</div>
        </div>

        <!-- Activité aujourd'hui -->
        <div class="stat bg-base-100 shadow-lg rounded-lg p-6 border-l-4 border-accent">
            <div class="stat-title text-lg">Activité aujourd'hui</div>
            <div class="stat-value text-3xl">{{ $movementsToday }}</div>
            <div class="stat-desc">{{ now()->format('d/m/Y') }}</div>
        </div>

        <!-- Total période -->
        <div class="stat bg-base-100 shadow-lg rounded-lg p-6 border-l-4 border-info">
            <div class="stat-title text-lg">Opérations période</div>
            <div class="stat-value text-3xl">{{ $movementsPeriod }}</div>
            <div class="stat-desc">{{ $entrees > 0 ? number_format($entrees, 0, ',', ' ') . '€ entrées' : 'Aucune activité' }}</div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
        <!-- Graphique flux quotidien -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h3 class="card-title">Flux financiers quotidiens</h3>
                <canvas id="fluxChart" height="250"></canvas>
            </div>
        </div>

        <!-- Répartition par type -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h3 class="card-title">Répartition par type d'opération</h3>
                <canvas id="typeChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Statistiques détaillées -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
        <!-- Contre-bons -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h3 class="card-title">📊 Contre-bons</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Nombre:</span>
                        <span class="font-semibold">{{ $contreBonsTotal }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Montant total:</span>
                        <span class="font-semibold">{{ number_format($contreBonsMontant, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between {{ $contreBonsEcart != 0 ? 'text-warning' : '' }}">
                        <span>Écart total:</span>
                        <span class="font-semibold">{{ number_format($contreBonsEcart, 2, ',', ' ') }} €</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bons de recouvrement -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h3 class="card-title">💰 Recouvrements</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Bons traités:</span>
                        <span class="font-semibold">{{ $bonsRecouvrementTotal }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Montant total:</span>
                        <span class="font-semibold">{{ number_format($bonsRecouvrementMontant, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Clients uniques:</span>
                        <span class="font-semibold">{{ $clientsRecouvres }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dépenses -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h3 class="card-title">💸 Dépenses</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Nombre:</span>
                        <span class="font-semibold">{{ $depensesTotal }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Montant total:</span>
                        <span class="font-semibold">{{ number_format($depensesMontant, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Moyenne:</span>
                        <span class="font-semibold">{{ $depensesTotal > 0 ? number_format($depensesMontant/$depensesTotal, 2, ',', ' ') : 0 }} €</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transferts -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h3 class="card-title">🔄 Transferts</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Total:</span>
                        <span class="font-semibold">{{ $transfertsTotal }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Montant total:</span>
                        <span class="font-semibold">{{ number_format($transfertsMontant, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Validés:</span>
                        <span class="font-semibold">{{ $transfertsValides }}/{{ $transfertsTotal }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chèques -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h3 class="card-title">🏦 Chèques</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Nombre:</span>
                        <span class="font-semibold">{{ $chequesTotal }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Montant total:</span>
                        <span class="font-semibold">{{ number_format($chequesMontant, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Clients uniques:</span>
                        <span class="font-semibold">{{ $chequesClients }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Raccourcis actions -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h3 class="card-title">⚡ Actions rapides</h3>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('expenses.create') }}" class="btn btn-outline btn-sm">+ Dépense</a>
                    <a href="{{ route('contre-bons.create') }}" class="btn btn-outline btn-sm">+ Contre-bon</a>
                    <a href="{{ route('transfers.create') }}" class="btn btn-outline btn-sm">+ Transfert</a>
                  {{--   <a href="{{ route('bons.create') }}" class="btn btn-outline btn-sm">+ Bon</a> --}}
                    <a href="{{ route('movements.index') }}" class="btn btn-outline btn-sm col-span-2">Voir tous les mouvements</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Dernières activités -->
    <div class="card bg-base-100 shadow-lg">
        <div class="card-body">
            <h3 class="card-title">📈 Dernières activités</h3>
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-right">Montant</th>
                            <th>Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentMovements as $m)
                        <tr>
                            <td>{{ $m->date_mvt->format('d/m H:i') }}</td>
                            <td>
                                <span class="badge badge-ghost badge-sm capitalize">
                                    {{ str_replace('_', ' ', $m->type) }}
                                </span>
                            </td>
                            <td class="max-w-xs truncate">{{ $m->description }}</td>
                            <td class="text-right font-mono {{ in_array($m->type, ['recette','transfert_credit']) ? 'text-success' : 'text-error' }}">
                                {{ number_format($m->montant, 2, ',', ' ') }} €
                            </td>
                            <td>
                                @if($m->source)
                                    <span class="badge badge-sm">
                                        {{ class_basename($m->source) }} #{{ $m->source->numero ?? 'N/A' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-gray-500 py-4">Aucune activité récente</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique flux quotidien
    const fluxCtx = document.getElementById('fluxChart').getContext('2d');
    new Chart(fluxCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($dailyData->pluck('date')) !!},
            datasets: [
                {
                    label: 'Entrées',
                    data: {!! json_encode($dailyData->pluck('entrées')) !!},
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Sorties',
                    data: {!! json_encode($dailyData->pluck('sorties')) !!},
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    stacked: false,
                },
                y: {
                    beginAtZero: true,
                    stacked: false
                }
            }
        }
    });

    // Graphique répartition par type
    const typeCtx = document.getElementById('typeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($typeData->pluck('type')) !!},
            datasets: [{
                data: {!! json_encode($typeData->pluck('montant')) !!},
                backgroundColor: [
                    '#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
</script>
@endsection
