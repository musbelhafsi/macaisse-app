@extends('layouts.app')
@section('content')
<div class="max-w-5xl mx-auto space-y-4">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <h3 class="card-title">Contre‑bon {{ $contreBon->numero }}</h3>
                <div class="flex gap-2">
                    @if(!$contreBon->validated_at)
                        <form method="post" action="{{ route('contre-bons.validate', $contreBon) }}" class="join">
                            @csrf
                            @php($u = Auth::user())
                            <input type="hidden" name="_uses_current_cash" value="1" />
                            <span class="badge badge-outline join-item">
                                @if($u && $u->currentCash)
                                    Caisse: {{ $u->currentCash->name }}
                                @else
                                    Aucune caisse sélectionnée
                                @endif
                            </span>
                            <button class="btn btn-success join-item" type="submit" @disabled(!($u && $u->currentCash))>Valider</button>
                        </form>
                    @endif
                    <a class="btn btn-outline" href="{{ route('contre-bons.bordereau', $contreBon) }}" target="_blank">Bordereau (HTML)</a>
                </div>
            </div>
            <p class="opacity-80">Date: {{ $contreBon->date }} | Montant: {{ number_format($contreBon->montant, 2, ',', ' ') }} | # Bons: {{ $contreBon->nombre_bons }} | Ecart: {{ number_format($contreBon->ecart, 2, ',', ' ') }} @if($contreBon->validated_at) | Validé le {{ $contreBon->validated_at }} @endif</p>
            @if(!$contreBon->validated_at)
            <form method="post" action="{{ route('contre-bons.update', $contreBon) }}" class="grid md:grid-cols-3 gap-4 items-end">
                @csrf
                @method('PUT')
                <div>
                    <x-input-label value="Numéro" />
                    <x-text-input name="numero" value="{{ $contreBon->numero }}" required />
                </div>
                <div>
                    <x-input-label value="Date" />
                    <x-text-input type="date" name="date" value="{{ $contreBon->date }}" required />
                </div>
                <div>
                    <x-input-label value="Montant (attendu)" />
                    <x-text-input type="number" step="0.01" name="montant" value="{{ number_format($contreBon->montant, 2, '.', '') }}" required />
                </div>
                <div>
                    <x-input-label value="Note" />
                    <input name="note" class="input input-bordered w-full" value="{{ $contreBon->note }}" />
                </div>
                <div class="md:col-span-3">
                    <button class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
            @endif
        </div>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-4 space-y-4">
            <h4 class="font-semibold">Bons inclus</h4>
            @if(!$contreBon->validated_at)
            <form method="post" action="{{ route('contre-bons.add-bon', $contreBon) }}" class="grid md:grid-cols-5 gap-2 items-end">
                @csrf
                <div>
                    <x-input-label value="Numéro" />
                    <x-text-input name="numero" required />
                </div>
                <div>
                    <x-input-label value="Client" />
                    <select name="client_id" class="select select-bordered w-full" required>
                        <option value="">--</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label value="Type" />
                    <select name="type" class="select select-bordered w-full" required>
                        <option value="espece">Espèce</option>
                        <option value="cheque">Chèque</option>
                    </select>
                </div>
                <div>
                    <x-input-label value="Montant" />
                    <x-text-input name="montant" type="number" step="0.01" min="0.01" required />
                </div>
                <div>
                    <x-input-label value="Note" />
                    <x-text-input name="note" />
                </div>
                <div class="md:col-span-5">
                    <button class="btn btn-primary">Ajouter la ligne</button>
                </div>
            </form>
            @endif

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th class="text-right">Montant</th>
                        @if(!$contreBon->validated_at)
                        <th></th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($bons as $b)
                        <tr>
                            <td>{{ $b->numero }}</td>
                            <td>{{ optional($b->client)->name }}</td>
                            <td>{{ $b->type }}</td>
                            <td class="text-right">{{ number_format($b->montant, 2, ',', ' ') }}</td>
                            @if(!$contreBon->validated_at)
                            <td>
                                <form method="post" action="{{ route('contre-bons.remove-bon', [$contreBon, $b]) }}" onsubmit="return confirm('Supprimer cette ligne ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-error">Supprimer</button>
                                </form>
                            </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection