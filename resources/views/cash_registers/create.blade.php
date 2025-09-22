@extends('layouts.app')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title">Nouvelle caisse</h3>
            <form method="post" action="{{ route('cash-registers.store') }}" class="space-y-4">
                @csrf
                <div class="form-control">
                    <x-input-label value="Nom" />
                    <x-text-input name="name" required />
                </div>
                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="is_main" value="1" class="checkbox" />
                        <span class="label-text">Principale</span>
                    </label>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <x-input-label value="Devise" />
                        <select name="currency" class="select select-bordered w-full" required>
                            <option value="DA">DA</option>
                            <option value="EUR">EUR</option>
                            <option value="USD">USD</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <x-input-label value="Solde initial" />
                        <x-text-input type="number" step="0.01" name="balance" value="0" />
                    </div>
                </div>
                <div class="flex gap-2">
                    <x-primary-button>Enregistrer</x-primary-button>
                    <a class="btn" href="{{ route('cash-registers.index') }}">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection