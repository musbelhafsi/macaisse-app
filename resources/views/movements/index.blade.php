@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-4">
    <!-- En-tête avec nom de caisse et boutons -->
    <div class="flex items-center justify-between">
        <span class="badge badge-outline badge-lg h-auto align-middle">
            Brouillard de Caisse: {{ $currentCash?->name ?? '—' }}
        </span>
        <div class="join">
            <a class="btn join-item" href="{{ route('expenses.create') }}">+ Dépense</a>
            <a class="btn join-item" href="{{ route('contre-bons.create') }}">+ Recouvrement</a>
            <a class="btn join-item" href="{{ route('cheques.create') }}">+ Chèque</a>
            <a class="btn join-item" href="{{ route('transfers.create') }}">+ Transfert</a>
        </div>
    </div>

    <!-- Message de succès -->
    @if(session('success'))
        <div class="alert alert-success mb-4">
            <div class="flex items-center">
                <span>✅</span>
                <span class="ml-2">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Carte de filtres -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="flex items-center justify-between">                
                <form method="get" action="" class="grid md:grid-cols-1 gap-4 items-end">
                    <div class="form-control">
                        <x-input-label value="Recherche" />
                        <x-text-input name="search" value="{{ request('search') }}" 
                            class="input input-bordered w-full max-w-xs" placeholder="Rechercher..."/>
                    </div>
                    <div class="grid md:grid-cols-4 gap-4 items-end"> 
                        <div class="form-control">
                            <x-input-label value="Type" />
                            <select name="type" class="select select-bordered w-full">
                                <option value="">Tous</option>
                                @foreach(['recette','depense','transfert_debit','transfert_credit','ajustement'] as $t)
                                    <option value="{{ $t }}" {{ request('type')==$t?'selected':'' }}>
                                        {{ ucfirst(str_replace('_',' ', $t)) }}
                                    </option>
                                @endforeach
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
                        <div>
                            <button class="btn btn-primary w-full" type="submit">Filtrer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cartes des totaux -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="stat bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="stat-title text-green-800 font-semibold">Total Entrées</div>
            <div class="stat-value text-green-600 text-xl">{{ number_format($totalEntrees, 2, ',', ' ') }} €</div>
            <div class="stat-desc text-green-600">{{ $countOperations }} opérations</div>
        </div>
        
        <div class="stat bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="stat-title text-red-800 font-semibold">Total Sorties</div>
            <div class="stat-value text-red-600 text-xl">{{ number_format($totalSorties, 2, ',', ' ') }} €</div>
            <div class="stat-desc text-red-600">Sur la période</div>
        </div>
        
        <div class="stat bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="stat-title text-blue-800 font-semibold">Solde Net</div>
            <div class="stat-value text-blue-600 text-xl">{{ number_format($soldeFinal, 2, ',', ' ') }} €</div>
            <div class="stat-desc @if($soldeFinal >= 0) text-blue-600 @else text-red-600 @endif">
                @if($soldeFinal >= 0) Excédent @else Déficit @endif
            </div>
        </div>

        <div class="stat bg-purple-50 border border-purple-200 rounded-lg p-4">
            <div class="stat-title text-purple-800 font-semibold">Solde Actuel</div>
            <div class="stat-value text-purple-600 text-xl">
                {{ number_format($currentCash?->balance ?? 0, 2, ',', ' ') }} €
            </div>
            <div class="stat-desc text-purple-600">Solde de la caisse</div>
        </div>
    </div>

    <!-- Tableau avec entêtes fixes -->
    <div class="h-[70vh] flex flex-col bg-white rounded-lg shadow border border-gray-300">
        <div class="flex-1 overflow-auto relative">
            <table class="w-full text-xs font-mono">
                <thead class="sticky top-0 z-10">
                    <tr class="text-xs uppercase tracking-wider text-gray-700 bg-gray-100 border-b border-gray-300">
                        <th class="w-24 px-4 py-3 text-left border-r border-dashed border-gray-300">Date</th>
                        <th class="w-32 px-4 py-3 text-left border-r border-dashed border-gray-300">Pièce</th>
                        <th class="w-28 px-4 py-3 text-left border-r border-dashed border-gray-300">Type</th>
                        <th class="px-4 py-3 text-left border-r border-dashed border-gray-300">Description</th>
                        <th class="w-24 px-4 py-3 text-right border-r border-dashed border-gray-300">Entrée</th>
                        <th class="w-24 px-4 py-3 text-right border-r border-dashed border-gray-300">Sortie</th>
                        <th class="w-24 px-4 py-3 text-right border-r border-dashed border-gray-300">Solde</th>
                        <th class="w-20 px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($items as $m)
                        @php
                            $isCredit = in_array($m->type, ['recette','transfert_credit']) || ($m->type === 'ajustement' && $m->montant >= 0);
                            $credit = $isCredit ? $m->montant : 0;
                            $debit = $isCredit ? 0 : $m->montant;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors duration-150
                            @switch($m->type)
                                @case('transfert_debit') text-red-700 @break
                                @case('transfert_credit') text-blue-700 @break
                                @case('depense') text-yellow-700 @break
                                @case('recette') text-green-700 @break
                                @default text-gray-700
                            @endswitch
                        ">
                            <td class="w-24 px-4 py-3 whitespace-nowrap border-r border-dashed border-gray-200">
                                {{ $m->date_mvt ? \Carbon\Carbon::parse($m->date_mvt)->format('d/m/Y') : '' }}
                            </td>
                            <td class="w-32 px-4 py-3 border-r border-dashed border-gray-200">
                                @if($m->source)
                                    @php
                                        $sourceType = class_basename($m->source_type);
                                        $sourceName = match($sourceType) {
                                            'Expense' => 'Dépense',
                                            'Transfer' => 'Transfert',
                                            'ContreBon' => 'Contre-bon',
                                            'Cheque' => 'Chèque',
                                            default => $sourceType
                                        };
                                    @endphp
                                    <span class="badge badge-sm">{{ $sourceName }} #{{ $m->source->numero ?? 'N/A' }}</span>
                                @endif
                            </td>
                            <td class="w-28 px-4 py-3 border-r border-dashed border-gray-200">
                                <span class="badge badge-ghost badge-sm capitalize">
                                    {{ str_replace('_', ' ', $m->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 border-r border-dashed border-gray-200">{{ $m->description }}</td>
                            <td class="w-24 px-4 py-3 text-right whitespace-nowrap border-r border-dashed border-gray-200">
                                @if($credit)
                                    <span class="text-green-600 font-medium">
                                        {{ number_format($credit, 2, ',', ' ') }}
                                    </span>
                                @endif
                            </td>
                            <td class="w-24 px-4 py-3 text-right whitespace-nowrap border-r border-dashed border-gray-200">
                                @if($debit)
                                    <span class="text-red-600 font-medium">
                                        {{ number_format($debit, 2, ',', ' ') }}
                                    </span>
                                @endif
                            </td>
                            <td class="w-24 px-4 py-3 text-right font-medium whitespace-nowrap border-r border-dashed border-gray-200">
                                {{ number_format($m->balance, 2, ',', ' ') }}
                            </td>
                            <td class="w-20 px-4 py-3 text-center">
                                <!-- Bouton Édition -->
                                <div class="flex flex-col space-y-1">

                                <a href="{{ route('movements.edit', $m) }}" 
                                   class="btn btn-xs btn-outline btn-primary"
                                   title="Modifier cette opération">
                                    ✏️
                                </a>
                                <!-- Bouton Annulation (si pas déjà annulé) -->
                                    @if(!$m->annule)
                                    <a href="{{ route('movements.annuler.form', $m) }}" class="btn btn-xs btn-outline btn-error" title="Annuler">
                                        🚫
                                    </a>
                                    @else
                                    <span class="badge badge-error badge-xs">Annulé</span>
                                    @endif
                                </div>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center opacity-70">Aucun mouvement trouvé</td>
                        </tr>
                    @endforelse
                    
                    <!-- Ligne des totaux -->
                    @if($items->count() > 0)
                    <tr class="bg-gray-800 text-white font-bold">
                        <td colspan="4" class="px-4 py-3 text-right uppercase">TOTAUX (filtres appliqués):</td>
                        <td class="w-24 px-4 py-3 text-right whitespace-nowrap border-r border-gray-400">
                            <span class="text-green-300">{{ number_format($totalEntrees, 2, ',', ' ') }}</span>
                        </td>
                        <td class="w-24 px-4 py-3 text-right whitespace-nowrap border-r border-gray-400">
                            <span class="text-red-300">{{ number_format($totalSorties, 2, ',', ' ') }}</span>
                        </td>
                        <td class="w-24 px-4 py-3 text-right whitespace-nowrap border-r border-gray-400">
                            <span class="@if($soldeFinal >= 0) text-green-300 @else text-red-300 @endif">
                                {{ number_format($soldeFinal, 2, ',', ' ') }}
                            </span>
                        </td>
                        <td class="w-20 px-4 py-3 text-center"></td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="flex-none p-4 bg-gray-50 border-t border-gray-300">
            {{ $items->links() }}
        </div>
    </div>
</div>

<style>
.sticky {
    position: sticky;
    top: 0;
    z-index: 10;
}

table {
    table-layout: fixed;
}

.stat {
    transition: all 0.3s ease;
}

.stat:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

/* Style de scroll amélioré */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
@endsection
