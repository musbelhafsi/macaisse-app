@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold">Bons de recouvrement</h3>
        <div class="tooltip" data-tip="La création se fait désormais depuis l'écran Contre‑bon">
            <button class="btn" disabled>Nouveau bon</button>
        </div>
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
                        <th>Client</th>
                        <th>Montant</th>
                        <th>Type</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($bons as $b)
                        <tr>
                            <td>{{ $b->id }}</td>
                            <td>{{ $b->numero }}</td>
                            <td>{{ $b->date_recouvrement }}</td>
                            <td>{{ optional($b->client)->name }}</td>
                            <td>{{ number_format($b->montant, 2, ',', ' ') }}</td>
                            <td>{{ $b->type }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $bons->links() }}
            </div>
        </div>
    </div>
</div>
@endsection