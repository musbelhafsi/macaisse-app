@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold">Contre‑bons</h3>
        <a class="btn btn-primary" href="{{ route('contre-bons.create') }}">Nouveau contre‑bon</a>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-4">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Numéro</th>
                            <th>Date</th>
                            <th>Note</th>
                            <th>Montant</th>
                            <th># Bons</th>
                            <th>Ecart</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($contreBons as $cb)
                        <tr>
                            <td>{{ $cb->id }}</td>
                            <td><a class="link" href="{{ route('contre-bons.show', $cb) }}">{{ $cb->numero }}</a></td>
                            <td>{{ $cb->date }}</td>
                            <td>{{ $cb->note }}</td>
                            <td>{{ number_format($cb->montant, 2, ',', ' ') }}</td>
                            <td>{{ $cb->nombre_bons }}</td>
                            <td>{{ number_format($cb->ecart, 2, ',', ' ') }}</td>
                            <td class="flex gap-2">
                                <a class="btn btn-xs" href="{{ route('contre-bons.show', $cb) }}">Voir</a>
                                @if(!$cb->validated_at)
                                    <a class="btn btn-xs" href="{{ route('contre-bons.edit', $cb) }}">Éditer</a>
                                    <form method="post" action="{{ route('contre-bons.destroy', $cb) }}" onsubmit="return confirm('Supprimer ce contre‑bon ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-xs btn-error">Supprimer</button>
                                    </form>
                                    @if($cb->nombre_bons > 0)
                                        @php($u = Auth::user())
                                        <form method="post" action="{{ route('contre-bons.validate', $cb) }}" class="join">
                                            @csrf
                                            <input type="hidden" name="_uses_current_cash" value="1" />
                                            <span class="badge badge-outline join-item">
                                                @if($u && $u->currentCash)
                                                    Caisse: {{ $u->currentCash->name }}
                                                @else
                                                    Aucune caisse sélectionnée
                                                @endif
                                            </span>
                                            <button class="btn btn-xs btn-success join-item" type="submit" @disabled(!($u && $u->currentCash))>Valider</button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $contreBons->links() }}
            </div>
        </div>
    </div>
</div>
@endsection