@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title">Nouveau bon de recouvrement</h3>
            <form method="post" action="{{ route('bons.store') }}" class="space-y-4">
                @csrf
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <x-input-label value="Numéro" />
                        <x-text-input name="numero" required />
                    </div>
                    <div class="form-control">
                        <x-input-label value="Date recouvrement" />
                        <x-text-input type="date" name="date_recouvrement" value="{{ date('Y-m-d') }}" required />
                    </div>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <x-input-label value="Société" />
                        <select name="company_id" class="select select-bordered w-full" required>
                            <option value="">--</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <x-input-label value="Livreur" />
                        <select name="livreur_id" class="select select-bordered w-full" required>
                            <option value="">--</option>
                            @foreach($livreurs as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="form-control md:col-span-2">
                        <x-input-label value="Client" />
                        <select name="client_id" class="select select-bordered w-full" required>
                            <option value="">--</option>
                            @foreach($clients as $cl)
                                <option value="{{ $cl->id }}">{{ $cl->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <x-input-label value="Montant" />
                        <x-text-input type="number" step="0.01" name="montant" required />
                    </div>
                    <div class="form-control">
                        <x-input-label value="Type" />
                        <select name="type" class="select select-bordered w-full" required>
                            <option value="espece">Espèce</option>
                            <option value="cheque">Chèque</option>
                        </select>
                    </div>
                </div>
                <div class="form-control">
                    <x-input-label value="Note" />
                    <textarea name="note" class="textarea textarea-bordered w-full"></textarea>
                </div>
                <div class="flex gap-2">
                    <x-primary-button>Enregistrer</x-primary-button>
                    <a class="btn" href="{{ route('bons.index') }}">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection