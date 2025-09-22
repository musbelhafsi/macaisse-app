@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-4">

    <h3 class="text-2xl font-bold mb-4">Mes recouvrements</h3>
    </div>

    <!-- Formulaire de filtre -->
    <div class="card bg-base-100 shadow">
    <div class="card-body">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">Mes recouvrements</h3>
            
            <form method="get" action="" class="grid md:grid-cols-4 gap-4 items-end">
                <div class="form-control">
                    <x-input-label value="Type" />
                    <select name="type" class="select select-bordered w-full">
                        <option value="">Tous</option>
                        <option value="espece" {{ request('type')==='espece'?'selected':'' }}>Espèces</option>
                        <option value="cheque" {{ request('type')==='cheque'?'selected':'' }}>Chèques</option>
                    </select>
                </div>

                <div class="form-control">
                    <x-input-label value="Du" />
                    <x-text-input type="date" name="from" value="{{ request('from') }}" />
                </div>

                <div class="form-control">
                    <x-input-label value="Au" />
                    <x-text-input type="date" name="to" value="{{ request('to') }}" />
                </div>

                <div>
                    <button type="submit" class="btn btn-primary w-full">Filtrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
    <!-- Tableau -->
    <div class="card bg-base-100 shadow">
        <div class="card-body p-4">
            <div class="overflow-x-auto">
        <table class="table table-zebra w-full">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Numéro</th>
                    <th>Client</th>
                    <th>Société</th>
                    <th class="text-right">Montant</th>
                </tr>
            </thead>
            <tbody>
            @foreach($allRecouvrements as $b)
                <tr>
                    <td>{{ $b->date_recouvrement }}</td>
                    <td>{{ $b->type }}</td>
                    <td>{{ $b->numero }}</td>
                    <td>{{ optional($b->client)->name }}</td>
                    <td>{{ optional($b->company)->name }}</td>
                    <td class="text-right">{{ number_format($b->montant,2,',',' ') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $allRecouvrements->links() }}
    </div>
</div>
</div>

@endsection
