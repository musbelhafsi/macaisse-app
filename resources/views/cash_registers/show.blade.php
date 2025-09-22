@extends('layouts.app')
@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-xl font-semibold">Caisse: {{ $cashRegister->name }}</h3>
        <div class="space-x-2">
            <a class="btn" href="{{ route('cash-registers.index') }}">Retour</a>
            <a class="btn btn-primary" href="{{ route('cash-registers.edit', $cashRegister) }}">Modifier</a>
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-4 mb-6">
        <div class="card bg-base-100 shadow">
            <div class="card-body p-4">
                <div class="text-sm opacity-70">Devise</div>
                <div class="text-lg font-medium">{{ $cashRegister->currency }}</div>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body p-4">
                <div class="text-sm opacity-70">Principale</div>
                <div class="text-lg font-medium">{{ $cashRegister->is_main ? 'Oui' : 'Non' }}</div>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body p-4">
                <div class="text-sm opacity-70">Solde</div>
                <div class="text-lg font-medium">{{ number_format($cashRegister->balance,2,',',' ') }}</div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow mb-6">
        <div class="card-body p-4">
            <h4 class="text-lg font-semibold mb-3">Ajouter un utilisateur à cette caisse</h4>
            <form method="post" action="{{ route('cash-registers.attach-user', $cashRegister) }}" class="flex gap-3 items-end">
                @csrf
                <div class="form-control w-full max-w-sm">
                    <x-input-label value="Utilisateur" />
                    <select class="select select-bordered w-full" name="user_id" required>
                        <option value="" disabled selected>Choisir...</option>
                        @foreach($availableUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>
                <x-primary-button>Ajouter</x-primary-button>
            </form>
        </div>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-4">
            <h4 class="text-lg font-semibold mb-3">Utilisateurs ayant accès</h4>
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th class="w-40">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cashRegister->users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role }}</td>
                                <td class="space-x-2">
                                    <a class="btn btn-outline btn-sm" href="{{ route('users.edit', $user) }}">Modifier</a>
                                    <form action="{{ route('cash-registers.detach-user', [$cashRegister, $user]) }}" method="post" class="inline">
                                        @csrf @method('delete')
                                        <button class="btn btn-error btn-sm" onclick="return confirm('Retirer l\'accès ?')">Retirer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center opacity-70">Aucun utilisateur n'a accès à cette caisse.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection