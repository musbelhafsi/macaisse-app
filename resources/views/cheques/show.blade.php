@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold">Détails du chèque {{ $cheque->code_banque }}-{{ $cheque->numero }}</h3>
        <div class="flex gap-2">
            <a class="btn" href="{{ route('cheques.edit', $cheque) }}">Modifier</a>
            <form method="post" action="{{ route('cheques.destroy', $cheque) }}" onsubmit="return confirm('Supprimer ce chèque ?');">
                @csrf
                @method('delete')
                <button class="btn btn-error">Supprimer</button>
            </form>
        </div>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-base-content/70">Code banque</div>
                    <div class="font-medium">{{ $cheque->code_banque }}</div>
                </div>
                <div>
                    <div class="text-sm text-base-content/70">Numéro</div>
                    <div class="font-medium">{{ $cheque->numero }}</div>
                </div>
                <div>
                    <div class="text-sm text-base-content/70">Montant</div>
                    <div class="font-medium">{{ number_format($cheque->montant, 2, ',', ' ') }}</div>
                </div>
                <div>
                    <div class="text-sm text-base-content/70">Client</div>
                    <div class="font-medium">{{ optional($cheque->client)->name }}</div>
                </div>
                <div>
                    <div class="text-sm text-base-content/70">Société</div>
                    <div class="font-medium">{{ optional($cheque->company)->name }}</div>
                </div>
                <div>
                    <div class="text-sm text-base-content/70">Livreur</div>
                    <div class="font-medium">{{ optional($cheque->livreur)->name }}</div>
                </div>
                <div>
                    <div class="text-sm text-base-content/70">Échéance</div>
                    <div class="font-medium">{{ $cheque->echeance ? \Carbon\Carbon::parse($cheque->echeance)->format('d/m/Y') : '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-base-content/70">Date de recouvrement</div>
                    <div class="font-medium">{{ $cheque->date_recouvrement ? \Carbon\Carbon::parse($cheque->date_recouvrement)->format('d/m/Y') : '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-base-content/70">Statut</div>
                    <div class="font-medium">{{ $cheque->statut }}</div>
                </div>
            </div>
            <div class="mt-6">
                <a class="btn" href="{{ route('cheques.index') }}">Retour</a>
            </div>
        </div>
    </div>
</div>
@endsection