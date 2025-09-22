@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold">Dépenses</h3>
        <a class="btn btn-primary" href="{{ route('expenses.create') }}">Nouvelle dépense</a>
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
                        <th>Libellé</th>
                        <th>Montant</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($expenses as $e)
                        <tr>
                            <td>{{ $e->id }}</td>
                            <td>{{ $e->date }}</td>
                            <td>{{ $e->numero }}</td>
                            <td>{{ $e->libelle }}</td>
                            <td>{{ number_format($e->montant, 2, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $expenses->links() }}
            </div>
        </div>
    </div>
</div>
@endsection