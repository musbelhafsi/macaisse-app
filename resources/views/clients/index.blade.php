@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto">
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
        @if (session('ko'))
            <div class="alert alert-error">
                {{ session('ko') }}
            </div>
        @endif
   </div>   
   
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold">Clients</h3>
        <a class="btn btn-primary" href="{{ route('clients.create') }}">Nouveau client</a>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-4">
            <!--filter-- Table -->
<div class="mb-6">
    <form method="GET" action="{{ route('clients.index') }}" class="grid md:grid-cols-3 gap-4">
        
        <!-- Recherche texte -->
        <div class="form-control">
               <x-input-label value="Recherche" />
               <x-text-input name="search" value="{{ request('search') }}" oninput="this.value = this.value.toUpperCase();" placeholder="Rechercher..."/>
          
        </div>

        <!-- Tri -->
        <div class="form-control">
            {{-- <label for="sort_by" class="block text-sm font-medium">Trier par</label> --}}
            {{-- <select name="sort_by" id="sort_by" class="select select-bordered w-full max-w-xs"> --}}
               <x-input-label value="Trier par" />
               <select name="sort_by" class="select select-bordered w-full">
                <option value="">-- Choisir --</option>
                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date de création</option>
                <option value="updated_at" {{ request('sort_by') == 'updated_at' ? 'selected' : '' }}>Dernière modification</option>
                <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Nom (A→Z)</option>
            </select>
        </div>

        <!-- Boutons -->
        <div class="md:col-span-4 flex gap-2">
            <button type="submit" class="btn btn-primary">Appliquer</button>
            <a href="{{ route('clients.index') }}" class="btn btn-ghost">Réinitialiser</a>
        </div>
    </form>
</div>

    <!--end filter-->
   
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($items as $it)
                        <tr>
                            <td>{{ $it->id }}</td>
                            <td>{{ $it->code }}</td>
                            <td>{{ $it->name }}</td>
                            <td class="space-x-2">
                                <a class="btn btn-outline btn-sm" href="{{ route('clients.edit',$it) }}">Modifier</a>
                                <form method="post" action="{{ route('clients.destroy',$it) }}" class="inline">
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