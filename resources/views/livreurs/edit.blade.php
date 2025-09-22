@extends('layouts.app')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title">Modifier livreur</h3>
            <form method="post" action="{{ route('livreurs.update', $livreur) }}" class="space-y-4">
                @csrf @method('put')
                <div class="form-control">
                    <x-input-label value="Nom" />
                    <x-text-input name="name" value="{{ $livreur->name }}" required />
                </div>
                <div class="form-control">
                    <x-input-label value="Email" />
                    <x-text-input type="email" name="email" value="{{ $livreur->email }}" required />
                </div>
                <div class="flex gap-2">
                    <x-primary-button>Mettre à jour</x-primary-button>
                    <a class="btn" href="{{ route('livreurs.index') }}">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection