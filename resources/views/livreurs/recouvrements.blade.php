@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-2xl font-bold">Mes recouvrements</h3>
        
        <!-- Statistiques rapides -->
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-title">Total Recouvrements</div>
                <div class="stat-value text-primary">
                    {{ number_format($allRecouvrements->sum('montant'), 2, ',', ' ') }} DA
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire de filtre -->
    <div class="card bg-base-100 shadow mb-6">
        <div class="card-body">
            <form method="get" action="" class="grid md:grid-cols-5 gap-4 items-end">
                <div class="form-control">
                    <x-input-label value="Type" />
                    <select name="type" class="select select-bordered w-full">
                        <option value="">Tous</option>
                        <option value="espece" {{ request('type')==='espece'?'selected':'' }}>Espèces</option>
                        <option value="cheque" {{ request('type')==='cheque'?'selected':'' }}>Chèques</option>
                    </select>
                </div>

                <div class="form-control">
                    <x-input-label value="Du" />
                    <x-text-input type="date" name="from" value="{{ request('from') }}" />
                </div>

                <div class="form-control">
                    <x-input-label value="Au" />
                    <x-text-input type="date" name="to" value="{{ request('to') }}" />
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary flex-1">Filtrer</button>
                    <a href="{{ route('livreur.recouvrements') }}" class="btn btn-outline">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card bg-base-100 shadow">
        <div class="card-body p-4">
            @if($allRecouvrements->count() > 0)
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Numéro</th>
                            <th>Client</th>
                            <th>Société</th>
                            <th class="text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allRecouvrements as $b)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($b->date_recouvrement)->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge {{ $b->type === 'espèce' ? 'badge-success' : 'badge-info' }}">
                                    {{ $b->type }}
                                </span>
                            </td>
                            <td class="font-mono">{{ $b->numero }}</td>
                            <td>{{ optional($b->client)->name ?? 'N/A' }}</td>
                            <td>{{ optional($b->company)->name ?? 'N/A' }}</td>
                            <td class="text-right font-bold">
                                {{ number_format($b->montant, 2, ',', ' ') }} DA
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $allRecouvrements->links() }}
            </div>
            @else
            <div class="text-center py-8">
                <p class="text-gray-500">Aucun recouvrement trouvé pour les critères sélectionnés.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
