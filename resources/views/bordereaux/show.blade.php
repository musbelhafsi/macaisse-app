@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto space-y-4">
    <div class="card bg-base-100 shadow border border-neutral/10">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <h3 class="card-title text-primary">Bordereau d'envoi — {{ $bordereau->numero }}</h3>
                <div class="join">
                    <a class="btn btn-secondary join-item" href="{{ route('bordereaux.pdf', $bordereau->id) }}">PDF</a>
                    <form method="post" action="{{ route('bordereaux.email', $bordereau) }}" class="join-item flex gap-2">
                        @csrf
                        <input name="to" class="input input-bordered" placeholder="destinataire@exemple.com" required />
                        <button class="btn btn-primary">Envoyer</button>
                    </form>
                </div>
            </div>
            <p class="text-base-content/70">Date d'envoi: {{ $bordereau->date_envoi }} — Statut: <span class="text-secondary font-medium">{{ strtoupper($bordereau->status) }}</span></p>
            <p>{{ $bordereau->note }}</p>
        </div>
    </div>

    <div class="card bg-base-100 shadow border border-neutral/10">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead class="bg-primary/5">
                        <tr>
                            <th>Type</th>
                            <th>Référence</th>
                            <th class="text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bordereau->lignes as $l)
                            <tr class="hover:bg-primary/5">
                                <td>{{ $l->type }}</td>
                                <td>{{ $l->numero_ref }}</td>
                                <td class="text-right">{{ $l->montant ? number_format($l->montant,2,',',' ') : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow border border-neutral/10">
        <div class="card-body">
            <h4 class="font-semibold text-primary">Détail des contre‑bons</h4>
            @foreach($contreBons as $cb)
                <div class="mt-2">
                    <div class="font-semibold">{{ $cb->numero }} — Société: {{ optional($cb->company)->name }} — Livreur: {{ optional($cb->livreur)->name }}</div>
                    <div class="text-base-content/70">Date: {{ $cb->date }} — Montant attendu: {{ number_format($cb->montant,2,',',' ') }}</div>
                </div>
            @endforeach

            <h4 class="font-semibold mt-6 text-primary">Chèques</h4>
            @foreach($chequeLignes as $l)
                @php($ch = $l->reference_id ? ($cheques[$l->reference_id] ?? null) : null)
                <div class="mt-1">
                    {{ $ch ? $ch->numero : $l->numero_ref }} — <span class="text-secondary">{{ $ch ? $ch->code_banque : ($l->meta['code_banque'] ?? '') }}</span> — {{ number_format($ch ? $ch->montant : ($l->montant ?? 0),2,',',' ') }}
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection