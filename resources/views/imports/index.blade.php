@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Imports</h1>

    @if(session('status'))
        <div class="alert alert-success mb-4">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-4">
        <a href="{{ route('imports.create') }}" class="btn btn-primary">
            Nouvel import
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Entité</th>
                    <th>Fichier</th>
                    <th>Statut</th>
                    <th>Créé par</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($imports as $import)
                    <tr>
                        <td>{{ $import->id }}</td>
                        <td>{{ ucfirst(str_replace('_',' ', $import->entity)) }}</td>
                        <td>{{ basename($import->file_path) }}</td>
                        <td>
                            <span class="badge 
                                {{ $import->status === 'pending' ? 'badge-warning' : '' }}
                                {{ $import->status === 'processing' ? 'badge-info' : '' }}
                                {{ $import->status === 'completed' ? 'badge-success' : '' }}
                                {{ $import->status === 'failed' ? 'badge-error' : '' }}
                            ">
                                {{ $import->status }}
                            </span>
                        </td>
                        <td>{{ $import->creator?->name ?? '—' }}</td>
                        <td>{{ $import->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('imports.show', $import) }}" class="btn btn-sm btn-outline">
                                Détails
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Aucun import pour l’instant.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $imports->links() }}
    </div>
</div>
@endsection
