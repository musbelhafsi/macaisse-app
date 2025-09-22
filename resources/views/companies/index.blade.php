@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold">Sociétés</h3>
        <a class="btn btn-primary" href="{{ route('companies.create') }}">Nouvelle société</a>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-4">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Nom</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($items as $it)
                        <tr>
                            <td>{{ $it->id }}</td>
                            <td>{{ $it->code }}</td>
                            <td>{{ $it->name }}</td>
                            <td class="space-x-2">
                                <a class="btn btn-outline btn-sm" href="{{ route('companies.edit',$it) }}">Modifier</a>
                                <form method="post" action="{{ route('companies.destroy',$it) }}" class="inline">
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