@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Modifier l'opération de caisse</h1>
        <a href="{{ route('movements.index') }}" class="btn btn-ghost">← Retour</a>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            @if($cashMovement->source)
            <div class="alert alert-info mb-4">
                <div class="flex items-center">
                    <span class="font-semibold">Source :</span>
                    <span class="ml-2 badge badge-sm">
                        {{ class_basename($cashMovement->source) }} #{{ $cashMovement->source->numero ?? 'N/A' }}
                    </span>
                </div>
                <div class="text-sm mt-1">
                    La modification affectera également l'entité source.
                </div>
            </div>
            @endif

            <form action="{{ route('movements.update', $cashMovement) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Date *</span>
                        </label>
               <input type="date" name="date_mvt" 
                   value="{{ old('date_mvt', \Carbon\Carbon::parse($cashMovement->date_mvt)->format('Y-m-d')) }}" 
                   class="input input-bordered" required>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Montant *</span>
                        </label>
                        <input type="number" step="0.01" name="montant" 
                               value="{{ old('montant', abs($cashMovement->montant)) }}" 
                               class="input input-bordered" required>
                    </div>

                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text font-semibold">Description *</span>
                        </label>
                        <textarea name="description" class="textarea textarea-bordered h-24" required>
                            {{ old('description', $cashMovement->description) }}
                        </textarea>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Type</span>
                        </label>
                        <input type="text" class="input input-bordered bg-gray-100" readonly
                               value="{{ ucfirst(str_replace('_', ' ', $cashMovement->type)) }}">
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Solde avant</span>
                        </label>
                        <input type="text" class="input input-bordered bg-gray-100" readonly
                               value="{{ number_format($cashMovement->balance - $cashMovement->montant, 2, ',', ' ') }} €">
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-6">
                    <a href="{{ route('movements.index') }}" class="btn btn-outline">Annuler</a>
                    <button type="submit" class="btn btn-primary">💾 Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
