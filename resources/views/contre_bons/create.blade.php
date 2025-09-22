@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title">Nouveau contre‑bon</h3>
            <form method="post" action="{{ route('contre-bons.store') }}" class="space-y-4">
                @csrf
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <x-input-label value="Numéro" />
                        <x-text-input id="numero" name="numero" required />
                    </div>
                    <div class="form-control">
                        <x-input-label value="Date" />
                        <x-text-input id="date_cb" type="date" name="date" value="{{ date('Y-m-d') }}" required />
                    </div>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <x-input-label value="Société" />
                        <select id="company_id" name="company_id" class="select select-bordered w-full" required>
                            <option value="">--</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <x-input-label value="Livreur" />
                        <select id="livreur_id" name="livreur_id" class="select select-bordered w-full" required>
                            <option value="">--</option>
                            @foreach($livreurs as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                        <a class="btn btn-primary" href="{{ route('livreurs.create') }}" title="Nouveau livreur">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>
                </div>
                <p class="text-sm opacity-70">Ce contre‑bon regroupera automatiquement tous les bons non affectés de ce livreur/société pour la date choisie.</p>
                <div class="form-control">
                    <x-input-label value="Note" />
                    <textarea name="note" class="textarea textarea-bordered w-full"></textarea>
                </div>
                <div class="flex gap-2">
                    <x-primary-button>Créer</x-primary-button>
                    <a class="btn" href="{{ route('contre-bons.index') }}">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
(function(){
  const company = document.getElementById('company_id');
  const livreur = document.getElementById('livreur_id');
  const date = document.getElementById('date_cb');
  const numero = document.getElementById('numero');
  async function suggest(){
    if(!company.value || !livreur.value || !date.value) return;
    const url = new URL('{{ route('contre-bons.suggest-numero') }}', window.location.origin);
    url.searchParams.set('company_id', company.value);
    url.searchParams.set('livreur_id', livreur.value);
    url.searchParams.set('date', date.value);
    try{
      const r = await fetch(url);
      const j = await r.json();
      if(j.suggestion && !numero.value) numero.value = j.suggestion;
    }catch(e){ console.warn(e); }
  }
  company.addEventListener('change', suggest);
  livreur.addEventListener('change', suggest);
  date.addEventListener('change', suggest);
})();
</script>
@endsection