@extends('layouts.app')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title">Nouvel utilisateur</h3>
            <form method="post" action="{{ route('users.store') }}" class="space-y-4">
                @csrf
                <div class="form-control">
                    <x-input-label value="Nom" />
                    <x-text-input name="name" value="{{ old('name') }}" required />
                    @error('name') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="form-control">
                    <x-input-label value="Email" />
                    <x-text-input type="email" name="email" value="{{ old('email') }}" required />
                    @error('email') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="form-control">
                    <x-input-label value="Mot de passe" />
                    <x-text-input type="password" name="password" required />
                    @error('password') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="form-control">
                    <x-input-label value="Confirmer mot de passe" />
                    <x-text-input type="password" name="password_confirmation" required />
                </div>
                <div class="form-control">
    <x-input-label value="Rôle" />
    <select name="role" class="select select-bordered w-full" required>
          <option value="livreur" {{ old('role', $user->role) == 'livreur' ? 'selected' : '' }}>Livreur</option>
        <option value="caissier" {{ old('role') == 'caissier' ? 'selected' : '' }}>Caissier</option>
        <option value="resp_principale" {{ old('role') == 'resp_principale' ? 'selected' : '' }}>Responsable principale</option>
        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
    </select>
    @error('role') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
</div>
                <div class="flex gap-2">
                    <x-primary-button>Enregistrer</x-primary-button>
                    <a class="btn" href="{{ route('users.index') }}">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
