@extends('layouts.app')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title">Nouveau livreur</h3>
            <form method="post" action="{{ route('livreurs.store') }}" class="space-y-4">
                @csrf
                <div class="form-control">
                    <x-input-label value="Nom" />
                    <x-text-input name="name" required />
                </div>
                <div class="form-control">
                    <x-input-label value="Email" />
                    <x-text-input type="email" name="email" required />
                </div>
                <p class="text-sm opacity-70">Le mot de passe sera généré automatiquement.</p>
                <div class="flex gap-2">
                    <x-primary-button>Enregistrer</x-primary-button>
                    <a class="btn" href="{{ route('livreurs.index') }}">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection