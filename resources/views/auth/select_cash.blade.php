@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title">Choisissez votre caisse</h2>
            <form method="post" action="{{ route('auth.select-cash.store') }}" class="space-y-4">
                @csrf
                <div class="form-control">
                    <label class="label"><span class="label-text">Caisse</span></label>
                    <select name="cash_id" class="select select-bordered" required>
                        <option value="">—</option>
                        @foreach($cashes as $c)
                            <option value="{{ $c->id }}" @selected(Auth::user()?->current_cash_id===$c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <button type="submit" class="btn btn-primary">Continuer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection