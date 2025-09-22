@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold">Nouveau transfert</h3>
        <a class="btn btn-ghost" href="{{ route('transfers.index') }}">Retour</a>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-6">
            <form method="post" action="{{ route('transfers.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="form-control w-full">
                        <div class="label"><span class="label-text">Numéro</span></div>
                        <input class="input input-bordered w-full" name="numero" value="{{ old('numero') }}" required>
                    </label>

                    <label class="form-control w-full">
                        <div class="label"><span class="label-text">Montant</span></div>
                        <input class="input input-bordered w-full" type="number" step="0.01" name="montant" value="{{ old('montant') }}" required>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="form-control w-full">
                        <div class="label"><span class="label-text">De (caisse émettrice)</span></div>
                        <select class="select select-bordered w-full" name="from_cash_id" required>
                            <option value="">--</option>
                            @foreach($caisses as $c)
                                <option value="{{ $c->id }}" @selected(old('from_cash_id')==$c->id)>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="form-control w-full">
                        <div class="label"><span class="label-text">Vers (caisse receptrice)</span></div>
                        <select class="select select-bordered w-full" name="to_cash_id" required>
                            <option value="">--</option>
                            @foreach($caisses as $c)
                                <option value="{{ $c->id }}" @selected(old('to_cash_id')==$c->id)>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <label class="form-control">
                    <div class="label"><span class="label-text">Note</span></div>
                    <textarea class="textarea textarea-bordered" name="note">{{ old('note') }}</textarea>
                </label>

                <label class="flex items-center gap-2">
                    <input class="checkbox" type="checkbox" name="immediate" value="1" @checked(old('immediate'))>
                    <span>Valider immédiatement (recommandé pour Retrait bancaire)</span>
                </label>

                <div class="pt-2">
                    <button class="btn btn-primary" type="submit">Émettre</button>
                </div>

                @if ($errors->any())
                    <div class="alert alert-error mt-4">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection