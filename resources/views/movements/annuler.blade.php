@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto py-6">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title text-error">⚠️ Confirmer l'annulation</h2>
            
            <!-- Détails de l'opération -->
            <div class="bg-gray-100 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div><strong>Date:</strong> {{ $cashMovement->date_mvt->format('d/m/Y') }}</div>
                    <div><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $cashMovement->type)) }}</div>
                    <div><strong>Montant:</strong> {{ number_format(abs($cashMovement->montant), 2, ',', ' ') }} €</div>
                    <div><strong>Description:</strong> {{ $cashMovement->description }}</div>
                </div>
            </div>

            <form action="{{ route('movements.annuler', $cashMovement) }}" method="POST">
                @csrf
                
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-semibold">Raison de l'annulation *</span>
                    </label>
                    <textarea name="raison_annulation" class="textarea textarea-bordered h-24" 
                              placeholder="Expliquez la raison de l'annulation..." required></textarea>
                    @error('raison_annulation')
                        <span class="text-error text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="alert alert-warning mb-4">
                    <div class="flex items-center">
                        <span>⚠️</span>
                        <span class="ml-2">
                            <strong>Attention:</strong> Cette action créera un mouvement inverse et recalculera les soldes.
                            L'opération ne pourra pas être supprimée.
                        </span>
                    </div>
                </div>

                <div class="card-actions justify-end">
                    <a href="{{ route('movements.index') }}" class="btn btn-ghost">Retour</a>
                    <button type="submit" class="btn btn-error">
                        🚫 Confirmer l'annulation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
