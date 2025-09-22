@extends('layouts.app')
@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold text-primary">Bordereaux d'envoi</h3>
        <a class="btn btn-primary" href="{{ route('bordereaux.create') }}">+ Nouveau</a>
    </div>
    <div class="card bg-base-100 shadow border border-neutral/10">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead class="bg-primary/5">
                        <tr>
                            <th>Numéro</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $b)
                            <tr class="hover:bg-primary/5">
                                <td>{{ $b->numero }}</td>
                                <td>{{ $b->date_envoi }}</td>
                                <td>
                                    <span class="badge badge-outline border-secondary/30 text-secondary/90">{{ strtoupper($b->status) }}</span>
                                </td>
                                <td class="text-right">
                                    <a class="btn btn-sm btn-secondary" href="{{ route('bordereaux.show',['bordereau'=>$b->id]) }}">Ouvrir</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center opacity-70">Aucun bordereau</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $items->links() }}</div>
        </div>
    </div>
</div>
@endsection