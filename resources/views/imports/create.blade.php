@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Nouvel import</h1>

    <form action="{{ route('imports.store') }}" method="post" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div class="form-control">
            <label class="label">Entité</label>
            <div class="flex gap-2 items-center">
                <select id="entity-select" name="entity" class="select select-bordered w-full" required>
                    <option value="">-- Sélectionner une entité --</option>
                    @foreach($entities as $key => $label)
                        <option value="{{ $key }}" @selected(old('entity') == $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <a id="template-link" href="#" class="btn btn-outline" onclick="return false;">Télécharger le template CSV</a>
            </div>
            @error('entity')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
            <p class="text-sm text-gray-500 mt-1">CSV ou Excel (.xlsx). Le template inclut les en-têtes valides pour l’entité choisie.</p>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const select = document.getElementById('entity-select');
                const link = document.getElementById('template-link');
                const updateLink = () => {
                    const val = select.value;
                    if (val) {
                        link.href = `{{ route('imports.template') }}?entity=${encodeURIComponent(val)}`;
                        link.onclick = null;
                    } else {
                        link.href = '#';
                        link.onclick = function(){ return false; };
                    }
                };
                select.addEventListener('change', updateLink);
                updateLink();
            });
        </script>

        <div class="form-control">
            <label class="label">Fichier</label>
            <input type="file" name="file" class="file-input file-input-bordered w-full" required>
            @error('file')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end">
            <a href="{{ route('imports.index') }}" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary ml-2">Lancer l’import</button>
        </div>
    </form>
</div>
@endsection
