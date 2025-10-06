@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        {{-- <h3 class="text-lg font-bold mb-4 text-center uppercase tracking-wider">
        Brouillard de Caisse
        </h3> --}}
         @php($u = Auth::user())
                    <span class="badge badge-outline badge-lg h-auto align-middle" >Brouillard de Caisse: {{ $currentCash?->name ?? '—' }}</span>
        <div class="join">
            <a class="btn join-item" href="{{ route('expenses.create') }}">+ Dépense</a>
            <a class="btn join-item" href="{{ route('contre-bons.create') }}">+ Recouvrement</a>
            <a class="btn join-item" href="{{ route('cheques.create') }}">+ Chèque</a>
            <a class="btn join-item" href="{{ route('transfers.create') }}">+ Transfert</a>
        </div>
    </div>
                {{-- <div>
                    @php($u = Auth::user())
                    <span class="badge badge-outline badge-lg h-auto align-middle" >Caisse: {{ $currentCash?->name ?? '—' }}</span>
                </div> --}}

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="flex items-center justify-between">
                                
                <form method="get" action="" class="grid md:grid-cols-1 gap-4 items-end">
                    <!-- Recherche texte -->
                    <div class="form-control">
                        <x-input-label value="Recherche" />
                        <x-text-input name="search" value="{{ request('search') }}" class="input input-bordered w-full max-w-xs" placeholder="Rechercher..."/>
                    
                    </div>
                   <!-- Deuxième ligne avec les autres champs -->
    <div class="grid md:grid-cols-4 gap-4 items-end"> 
                    <div class="form-control">
                        <x-input-label value="Type" />
                        <select name="type" class="select select-bordered w-full">
                            <option value="">Tous</option>
                            @foreach(['recette','depense','transfert_debit','transfert_credit','ajustement'] as $t)
                                <option value="{{ $t }}" {{ request('type')==$t?'selected':'' }}>{{ ucfirst(str_replace('_',' ', $t)) }}</option>
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

    <!-- Tableau des mouvements -->
    <div class="h-screen flex flex-col">
    <div class="flex-1 overflow-hidden flex flex-col">
        <div class="overflow-auto flex-1 bg-white shadow-md border border-gray-400">
            <table class="w-full text-xs font-mono">
                <thead class="sticky top-0 bg-gray-100 border-b border-gray-400">
                    <tr class="text-xs uppercase tracking-wider text-gray-700">
                        <th class="w-24 px-4 py-2 text-left border-r border-dashed border-gray-300">Date</th>
                        <th class="w-32 px-4 py-2 text-left border-r border-dashed border-gray-300">Pièce</th>
                        <th class="w-28 px-4 py-2 text-left border-r border-dashed border-gray-300">Type</th>
                        <th class="w-auto px-4 py-2 text-left border-r border-dashed border-gray-300">Description</th>
                        <th class="w-24 px-4 py-2 text-right border-r border-dashed border-gray-300">Entrée</th>
                        <th class="w-24 px-4 py-2 text-right border-r border-dashed border-gray-300">Sortie</th>
                        <th class="w-24 px-4 py-2 text-right">Solde</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-300">
                    @forelse($items as $m)
                        @php(
                            $isCredit = in_array($m->type, ['recette','transfert_credit']) || ($m->type === 'ajustement' && $m->montant >= 0)
                        )
                        @php(
                            $credit = $isCredit ? $m->montant : 0
                        )
                        @php(
                            $debit = $isCredit ? 0 : $m->montant
                        )
                        <tr class="
                            @switch($m->type)
                                @case('transfert_debit')  text-red-700 @break
                                @case('transfert_credit') text-blue-700 @break
                                @case('depense')          text-yellow-700 @break
                                @case('recette')          text-green-700 @break
                                @default                  text-gray-700
                            @endswitch
                        ">
                            <td class="w-24 px-4 py-2 whitespace-nowrap border-r border-dashed border-gray-200">{{ $m->date_mvt ? \Carbon\Carbon::parse($m->date_mvt)->format('d/m/Y') : '' }}</td>
                            <td class="w-32 px-4 py-2 border-r border-dashed border-gray-200">
                                @php($pieceNumero = null)
                                @if($m->source_type === \App\Models\Expense::class)
                                    @php($pieceNumero = $m->source->numero ?? null)
                                    @if($pieceNumero)
                                        <span class="badge badge-sm">Dépense #{{ $pieceNumero }}</span>
                                    @endif
                                @elseif($m->source_type === \App\Models\Transfer::class)
                                    @php($pieceNumero = $m->source->numero ?? null)
                                    @if($pieceNumero)
                                        <span class="badge badge-sm">Transfert #{{ $pieceNumero }}</span>
                                    @endif
                                @elseif($m->source_type === \App\Models\ContreBon::class)
                                    @php($pieceNumero = $m->source->numero ?? null)
                                    @if($pieceNumero)
                                        <span class="badge badge-sm">Contre‑bon #{{ $pieceNumero }}</span>
                                    @endif
                                @endif
                            </td>
                            <td class="w-28 px-4 py-2 border-r border-dashed border-gray-200"><span class="badge badge-ghost badge-sm capitalize">{{ str_replace('_',' ', $m->type) }}</span></td>
                            <td class="w-auto px-4 py-2 border-r border-dashed border-gray-200">{{ $m->description }}</td>
                            <td class="w-24 px-4 py-2 text-right whitespace-nowrap border-r border-dashed border-gray-200">{{ $credit ? number_format($credit,2,',',' ') : '' }}</td>
                            <td class="w-24 px-4 py-2 text-right whitespace-nowrap border-r border-dashed border-gray-200">{{ $debit ? number_format($debit,2,',',' ') : '' }}</td>
                            <td class="w-24 px-4 py-2 text-right font-medium whitespace-nowrap">{{ number_format($m->balance,2,',',' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center opacity-70">Aucun mouvement</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-white border-t">
            {{ $items->links() }}
        </div>
    </div>
</div>

    <!-- End Table -->
</div>
@endsection