@extends('layouts.app')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title">Modifier société</h3>
            <form method="post" action="{{ route('companies.update', $company) }}" class="space-y-4">
                @csrf @method('put')
                <div class="form-control">
                    <x-input-label value="Code" />
                    <x-text-input name="code" value="{{ $company->code }}" required />
                    <x-input-error :messages="$errors->get('code')" />
                </div>
                <div class="form-control">
                    <x-input-label value="Nom" />
                    <x-text-input name="name" value="{{ $company->name }}" required />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div class="flex gap-2">
                    <x-primary-button>Mettre à jour</x-primary-button>
                    <a class="btn" href="{{ route('companies.index') }}">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection