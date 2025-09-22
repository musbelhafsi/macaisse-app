@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title">Nouveau contre‑bon</h3>
            <form method="post" action="{{ route('contre-bons.store') }}" class="grid md:grid-cols-2 gap-4 items-end">
                @csrf
                <div class="md:col-span-2">
                    <x-input-label value="Société" />
                    <select name="company_id" class="select select-bordered w-full" required>
                        <option value="">--</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <x-input-label value="Livreur" />
                    <select name="livreur_id" class="select select-bordered w-full" required>
                        <option value="">--</option>
                        @foreach($livreurs as $livreur)
                            <option value="{{ $livreur->id }}">{{ $livreur->name }}</option>
                        @endforeach
                    </select>
                     <a class="btn btn-primary" href="{{ route('livreurs.create') }}" title="Nouveau livreur">
                            <i class="fas fa-plus"></i>
                        </a>
                </div>
                <div>
                    <x-input-label value="Date" />
                    <x-text-input type="date" name="date" value="{{ date('Y-m-d') }}" required />
                </div>
                <div>
                    <x-input-label value="Numéro" />
                    <x-text-input name="numero" required />
                </div>
                <div>
                    <x-input-label value="Montant (attendu)" />
                    <x-text-input type="number" step="0.01" name="montant" value="0.00" required />
                </div>
                <div class="md:col-span-2">
                    <x-input-label value="Note" />
                    <input name="note" class="input input-bordered w-full" />
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button class="btn btn-primary">Créer</button>
                    <a class="btn" href="{{ route('contre-bons.index') }}">Annuler</a>
                </div>
            </form>
            <script>
                // Suggestion du numéro selon société/livreur/date
                document.addEventListener('DOMContentLoaded', function() {
                    const company = document.querySelector('select[name="company_id"]');
                    const livreur = document.querySelector('select[name="livreur_id"]');
                    const date = document.querySelector('input[name="date"]');
                    const numero = document.querySelector('input[name="numero"]');
                    async function suggest() {
                        if (!company.value || !livreur.value || !date.value) return;
                        try {
                            const params = new URLSearchParams({company_id: company.value, livreur_id: livreur.value, date: date.value});
                            const res = await fetch(`{{ route('contre-bons.suggest-numero') }}?` + params.toString());
                            const data = await res.json();
                            if (data.suggestion && !numero.value) numero.value = data.suggestion;
                        } catch (e) {}
                    }
                    [company, livreur, date].forEach(el => el.addEventListener('change', suggest));
                });
            </script>
        </div>
    </div>
</div>
@endsection