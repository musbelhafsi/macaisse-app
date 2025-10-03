@extends('layouts.app')
@section('content')
@php
    $activeTab = request('tab', 'filters');
@endphp
<div class="mb-4">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif
   </div>
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold">Chèques (portefeuille)</h3>
        <div class="join">
            <a class="btn join-item" href="{{ route('cheques.create') }}">Nouveau chèque</a>
            <a class="btn btn-ghost join-item" href="{{ route('cheques.export', request()->all()) }}">Exporter (Excel)</a>
        </div>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-4">
            <div class="mb-4 flex items-center justify-between">
                <div class="btn-group">
                    <a href="{{ route('cheques.index', array_merge(request()->except('page'), ['tab' => 'filters'])) }}"
                       class="btn btn-sm {{ $activeTab === 'filters' ? 'btn-primary' : 'btn-ghost' }}">Filtres</a>
                    <a href="{{ route('cheques.index', array_merge(request()->except('page'), ['tab' => 'adhoc'])) }}"
                       class="btn btn-sm {{ $activeTab === 'adhoc' ? 'btn-primary' : 'btn-ghost' }}">Saisie ad‑hoc</a>
                </div>
            </div>

            {{-- TAB: Filtres --}}
            <div class="mb-6 {{ $activeTab !== 'filters' ? 'hidden' : '' }}">
                <form method="get" action="{{ route('cheques.index') }}" class="grid md:grid-cols-4 gap-4">
                    <div class="form-control">
                        <x-input-label value="Code banque" />
                        <x-text-input name="code_banque" value="{{ request('code_banque') }}" />
                    </div>
                    <div class="form-control">
                        <x-input-label value="Numéro" />
                        <x-text-input name="numero" value="{{ request('numero') }}" />
                    </div>
                    <div class="form-control">
                        <x-input-label value="Client" />
                        <select name="client_id" class="select select-bordered w-full">
                            <option value="">--</option>
                            @foreach($clients as $cl)
                                <option value="{{ $cl->id }}" @selected((string)request('client_id') === (string)$cl->id)>{{ $cl->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <x-input-label value="Société" />
                        <select name="company_id" class="select select-bordered w-full">
                            <option value="">--</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}" @selected((string)request('company_id') === (string)$c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <x-input-label value="Livreur" />
                        <select name="livreur_id" class="select select-bordered w-full">
                            <option value="">--</option>
                            @foreach($livreurs as $u)
                                <option value="{{ $u->id }}" @selected((string)request('livreur_id') === (string)$u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <x-input-label value="Date de recouvrement (du)" />
                        <x-text-input type="date" name="date_recouvrement_from" value="{{ request('date_recouvrement_from') }}" />
                    </div>
                    <div class="form-control">
                        <x-input-label value="Date de recouvrement (au)" />
                        <x-text-input type="date" name="date_recouvrement_to" value="{{ request('date_recouvrement_to') }}" />
                    </div>
                    <div class="md:col-span-4 flex gap-2">
                        <button class="btn btn-primary">Afficher</button>
                        <a class="btn btn-ghost" href="{{ route('cheques.index', ['tab' => 'filters']) }}">Réinitialiser</a>
                    </div>
                </form>
            </div>

            {{-- TAB: Saisie ad‑hoc --}}
            <div class="mb-6 {{ $activeTab !== 'adhoc' ? 'hidden' : '' }}">
                <h4 class="font-semibold mb-3">Ajouter un chèque (ad‑hoc)</h4>
                <form method="post" action="{{ route('cheques.store') }}" class="grid md:grid-cols-3 gap-4">
                    @csrf
                    <div class="form-control">
                        <x-input-label value="Code banque" />
                        <x-text-input name="code_banque" required />
                    </div>
                    <div class="form-control">
                        <x-input-label value="Numéro" />
                        <x-text-input name="numero" required />
                    </div>
                    <div class="form-control">
                        <x-input-label value="Montant" />
                        <x-text-input type="number" step="0.01" name="montant" required />
                    </div>
                    <div class="form-control">
                        <x-input-label value="Client" />
                        <select name="client_id" class="select select-bordered w-full" required>
                            <option value="">--</option>
                            @foreach($clients as $cl)
                                <option value="{{ $cl->id }}">{{ $cl->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <x-input-label value="Société" />
                        <select name="company_id" class="select select-bordered w-full" required>
                            <option value="">--</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <x-input-label value="Livreur" />
                        <select name="livreur_id" class="select select-bordered w-full" required>
                            <option value="">--</option>
                            @foreach($livreurs as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <x-input-label value="Échéance (optionnel)" />
                        <x-text-input type="date" name="echeance" />
                    </div>
                    <div class="form-control">
                        <x-input-label value="Date de recouvrement (optionnel)" />
                        <x-text-input type="date" name="date_recouvrement" />
                    </div>
                    <div class="md:col-span-3">
                        <x-primary-button>Enregistrer</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra font-mono">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Banque</th>
                        <th>Numéro</th>
                        <th>Client</th>
                        <th>Livreur</th>
                        <th>Montant</th>
                        <th>Statut</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($cheques as $ch)
                        <tr>
                            <td><a class="link" href="{{ route('cheques.show', $ch) }}">{{ $ch->id }}</td>
                            <td>{{ $ch->date_recouvrement ? \Carbon\Carbon::parse($ch->date_recouvrement)->format('d/m/Y') : '' }}</td>
                            <td>{{ $ch->code_banque }}</td>
                            <td>{{ $ch->numero }}</td>
                            <td>{{ optional($ch->client)->name }}</td>
                            <td>{{ optional($ch->livreur)->name }}</td>
                            <td class="text-right">{{ number_format($ch->montant, 2, ',', ' ') }}</td>
                            <td>{{ $ch->statut }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $cheques->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>
</div>
@endsection