@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-xl font-semibold">Brouillard de caisse</h3>
        <div class="join">
            <a class="btn join-item" href="{{ route('expenses.create') }}">+ Dépense</a>
            <a class="btn join-item" href="{{ route('contre-bons.create') }}">+ Recouvrement</a>
            <a class="btn join-item" href="{{ route('cheques.create') }}">+ Chèque</a>
            <a class="btn join-item" href="{{ route('transfers.create') }}">+ Transfert</a>
        </div>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="flex items-center justify-between">
                
                <div>
                    @php($u = Auth::user())
                    <span class="badge badge-outline badge-lg h-auto align-middle" >Caisse: {{ $currentCash?->name ?? '—' }}</span>
                </div>
                <form method="get" action="" class="grid md:grid-cols-4 gap-4 items-end">
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
                </form>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Pièce</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-right">Entrée</th>
                            <th class="text-right">Sortie</th>
                            <th class="text-right">Solde</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php($balance = 0)
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
                        @php($balance = $balance + $credit - $debit)
                        <tr>
                            <td>{{ $m->date_mvt }}</td>
                            <td>
                                @php(
                                    $pieceNumero = null
                                )
                                @if($m->source_type === \App\Models\Expense::class)
                                    @php($pieceNumero = $m->source->numero ?? null)
                                    @if($pieceNumero)
                                        <span class="badge">Dépense #{{ $pieceNumero }}</span>
                                    @endif
                                @elseif($m->source_type === \App\Models\Transfer::class)
                                    @php($pieceNumero = $m->source->numero ?? null)
                                    @if($pieceNumero)
                                        <span class="badge">Transfert #{{ $pieceNumero }}</span>
                                    @endif
                                @elseif($m->source_type === \App\Models\ContreBon::class)
                                    @php($pieceNumero = $m->source->numero ?? null)
                                    @if($pieceNumero)
                                        <span class="badge">Contre‑bon #{{ $pieceNumero }}</span>
                                    @endif
                                @endif
                            </td>
                            <td><span class="badge badge-ghost capitalize">{{ str_replace('_',' ', $m->type) }}</span></td>
                            <td>{{ $m->description }}</td>
                            <td class="text-right">{{ $credit ? number_format($credit,2,',',' ') : '' }}</td>
                            <td class="text-right">{{ $debit ? number_format($debit,2,',',' ') : '' }}</td>
                            <td class="text-right">{{ number_format($balance,2,',',' ') }}</td>
                           
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center opacity-70">Aucun mouvement</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $items->links() }}
            </div>
        </div>
    </div>
</div>
@endsection