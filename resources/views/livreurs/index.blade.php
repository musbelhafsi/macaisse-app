@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold">Livreurs</h3>
        <a class="btn btn-primary" href="{{ route('livreurs.create') }}">Nouveau livreur</a>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-4">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($items as $u)
                        <tr>
                            <td>{{ $u->id }}</td>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->role }}</td>
                            <td class="space-x-2">
                                <a class="btn btn-outline btn-sm" href="{{ route('livreurs.edit',$u) }}">Modifier</a>
                                <form method="post" action="{{ route('livreurs.destroy',$u) }}" class="inline">
                                    @csrf @method('delete')
                                    <button class="btn btn-error btn-sm" onclick="return confirm('Supprimer ?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $items->links() }}
            </div>
        </div>
    </div>
</div>
@endsection