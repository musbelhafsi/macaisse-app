@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Détails de l’import #{{ $import->id }}</h1>

    <div class="card bg-base-100 shadow">
        <div class="card-body space-y-2">
            <p><strong>Entité :</strong> {{ ucfirst(str_replace('_',' ', $import->entity)) }}</p>
            <p><strong>Fichier :</strong> {{ basename($import->file_path) }}</p>
            <p><strong>Statut :</strong> 
                <span class="badge badge-lg 
                    {{ $import->status === 'pending' ? 'badge-warning' : '' }}
                    {{ $import->status === 'processing' ? 'badge-info' : '' }}
                    {{ $import->status === 'completed' ? 'badge-success' : '' }}
                    {{ $import->status === 'failed' ? 'badge-error' : '' }}
                ">
                    {{ $import->status }}
                </span>
            </p>
            <p><strong>Créé par :</strong> {{ $import->creator?->name ?? '—' }}</p>
            <p><strong>Date :</strong> {{ $import->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('imports.index') }}" class="btn btn-secondary">← Retour</a>
    </div>
</div>
@endsection
