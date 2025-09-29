@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold">Transferts</h3>
        <a class="btn btn-primary" href="{{ route('transfers.create') }}">Nouveau transfert</a>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-4">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Numéro</th>
                        <th>De</th>
                        <th>Vers</th>
                        <th>Montant</th>
                        <th>Reçu</th>
                        <th>Ecart</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($transfers as $t)
                        <tr>
                            <td>{{ $t->id }}</td>
                            <td>{{ $t->created_at->format('d/m/Y') }}</td>
                            <td>{{ $t->numero }}</td>
                            <td>{{$t->fromCash?->name ?? '—'}}</td>
                            <td>{{ $t->toCash?->name ?? '—' }}</td>
                            <td>{{ number_format($t->montant, 2, ',', ' ') }}</td>
                            <td>{{ $t->montant_recu ? number_format($t->montant_recu, 2, ',', ' ') : '-' }}</td>
                            <td>{{ number_format($t->ecart, 2, ',', ' ') }}</td>
                            <td>
                                <span class="badge badge-ghost capitalize">{{ $t->statut }}</span>
                            </td>
                            <td>
                                @if($t->statut === 'emis')
                                    <form class="flex items-center gap-2" method="post" action="{{ route('transfers.validate', $t) }}">
                                        @csrf
                                        <input class="input input-bordered input-sm w-36" type="number" step="0.01" name="montant_recu" placeholder="Montant reçu" required>
                                        <button class="btn btn-sm" type="submit">Valider</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $transfers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection