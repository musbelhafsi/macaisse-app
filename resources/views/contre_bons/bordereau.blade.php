@extends('layouts.app')
@section('content')
<article>
    <header>
        <h3>Bordereau — Contre‑bon {{ $contreBon->numero }}</h3>
        <p>Société: {{ optional($contreBon->company)->name }} | Livreur: {{ optional($contreBon->livreur)->name }} | Date: {{ $contreBon->date }}</p>
    </header>

    <h4>Récapitulatif</h4>
    <ul>
        <li>Total espèces: <strong>{{ number_format($total_especes, 2, ',', ' ') }} DA</strong></li>
        <li>Total chèques: <strong>{{ number_format($total_cheques, 2, ',', ' ') }} DA</strong></li>
        <li>Total bons: <strong>{{ number_format($contreBon->montant, 2, ',', ' ') }} DA</strong></li>
        <li>Écart: <strong>{{ number_format($contreBon->ecart, 2, ',', ' ') }} DA</strong></li>
    </ul>

    <h4>Bons de recouvrement</h4>
    <table>
        <thead>
        <tr>
            <th>Numéro</th>
            <th>Client</th>
            <th>Type</th>
            <th>Montant</th>
        </tr>
        </thead>
        <tbody>
        @foreach($bons as $b)
            <tr>
                <td>{{ $b->numero }}</td>
                <td>{{ optional($b->client)->name }}</td>
                <td>{{ $b->type }}</td>
                <td>{{ number_format($b->montant, 2, ',', ' ') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h4>Chèques en portefeuille envoyés</h4>
    <table>
        <thead>
        <tr>
            <th>Code banque</th>
            <th>Numéro</th>
            <th>Client</th>
            <th>Montant</th>
        </tr>
        </thead>
        <tbody>
        @foreach($cheques as $ch)
            <tr>
                <td>{{ $ch->code_banque }}</td>
                <td>{{ $ch->numero }}</td>
                <td>{{ optional($ch->client)->name }}</td>
                <td>{{ number_format($ch->montant, 2, ',', ' ') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</article>
@endsection