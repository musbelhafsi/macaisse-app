@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title">Modifier chèque</h3>
            <form method="post" action="{{ route('cheques.update', $cheque) }}" class="space-y-4">
                @csrf
                @method('put')

                <div class="grid md:grid-cols-3 gap-4">
                    <div class="form-control">
                        <x-input-label value="Code banque" />
                        <x-text-input name="code_banque" value="{{ old('code_banque', $cheque->code_banque) }}" required />
                        @error('code_banque')<div class="text-error text-sm">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-control">
                        <x-input-label value="Numéro" />
                        <x-text-input name="numero" value="{{ old('numero', $cheque->numero) }}" required />
                        @error('numero')<div class="text-error text-sm">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-control">
                        <x-input-label value="Montant" />
                        <x-text-input type="number" step="0.01" name="montant" value="{{ old('montant', $cheque->montant) }}" required />
                        @error('montant')<div class="text-error text-sm">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="grid md:grid-cols-3 gap-4">
                    <div class="form-control">
                        <x-input-label value="Client" />
                        <select name="client_id" class="select select-bordered w-full" required>
                            <option value="">--</option>
                            @foreach($clients as $cl)
                                <option value="{{ $cl->id }}" @selected((string)old('client_id', $cheque->client_id) === (string)$cl->id)>{{ $cl->name }}</option>
                            @endforeach
                        </select>
                        @error('client_id')<div class="text-error text-sm">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-control">
                        <x-input-label value="Société" />
                        <select name="company_id" class="select select-bordered w-full" required>
                            <option value="">--</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}" @selected((string)old('company_id', $cheque->company_id) === (string)$c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('company_id')<div class="text-error text-sm">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-control">
                        <x-input-label value="Livreur" />
                        <select name="livreur_id" class="select select-bordered w-full" required>
                            <option value="">--</option>
                            @foreach($livreurs as $u)
                                <option value="{{ $u->id }}" @selected((string)old('livreur_id', $cheque->livreur_id) === (string)$u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                        @error('livreur_id')<div class="text-error text-sm">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <x-input-label value="Échéance (optionnel)" />
                        <x-text-input type="date" name="echeance" value="{{ old('echeance', $cheque->echeance) }}" />
                        @error('echeance')<div class="text-error text-sm">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-control">
                        <x-input-label value="Date de recouvrement (optionnel)" />
                        <x-text-input type="date" name="date_recouvrement" value="{{ old('date_recouvrement', $cheque->date_recouvrement) }}" />
                        @error('date_recouvrement')<div class="text-error text-sm">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="flex gap-2">
                    <x-primary-button>Mettre à jour</x-primary-button>
                    <a class="btn" href="{{ route('cheques.index') }}">Annuler</a>
                    <a class="btn btn-ghost" href="{{ route('cheques.show', $cheque) }}">Détails</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection