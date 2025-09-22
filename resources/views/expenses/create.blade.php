@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title">Nouvelle dépense</h3>
            <form method="post" action="{{ route('expenses.store') }}" class="space-y-4">
                @csrf
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <x-input-label value="Date" />
                        <x-text-input type="date" name="date" value="{{ date('Y-m-d') }}" required />
                    </div>
                    <div class="form-control">
                        <x-input-label value="Numéro" />
                        <x-text-input name="numero" required />
                    </div>
                </div>
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="form-control">
                        <x-input-label value="Caisse" />
                        <select name="cash_id" class="select select-bordered w-full" required>
                            <option value="">--</option>
                            @foreach($caisses as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <x-input-label value="Libellé" />
                        <x-text-input name="libelle" required />
                    </div>
                    <div class="form-control">
                        <x-input-label value="Montant" />
                        <x-text-input type="number" step="0.01" name="montant" required />
                    </div>
                </div>
                <div class="form-control">
                    <x-input-label value="Note" />
                    <textarea name="note" class="textarea textarea-bordered w-full"></textarea>
                </div>
                <div class="flex gap-2">
                    <x-primary-button>Enregistrer</x-primary-button>
                    <a class="btn" href="{{ route('expenses.index') }}">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection