{{-- <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bordereau d'envoi — {{ $bordereau->numero }}</title>
    <style>
        /* Dompdf-friendly defaults */
        html, body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #000; }
        article, header, section { display: block; }
        h3, h4 { margin: 0 0 8px; }
        p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; }
        th { background: #f0f0f0; }
        .mt-20 { margin-top: 20px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
<article>
    <header>
        <h3>Bordereau d'envoi — {{ $bordereau->numero }}</h3>
        <p>Date: {{ $bordereau->date_envoi }}</p>
        @if($bordereau->note)
            <p>Note: {{ $bordereau->note }}</p>
        @endif
    </header>

    <section class="mt-20">
        <h4>Contre‑bons</h4>
        <table cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Société</th>
                    <th>Livreur</th>
                    <th>Date</th>
                    <th>Montant attendu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contreBons as $cb)
                    <tr>
                        <td>{{ $cb->numero }}</td>
                        <td>{{ optional($cb->company)->name }}</td>
                        <td>{{ optional($cb->livreur)->name }}</td>
                        <td>{{ $cb->date }}</td>
                        <td class="text-right">{{ number_format($cb->montant, 2, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <section class="mt-20">
        <h4>Chèques</h4>
        <table cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Banque</th>
                    <th>Montant</th>
                    <th>Échéance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chequeLignes as $l)
                    @php($ch = $l->reference_id ? ($cheques[$l->reference_id] ?? null) : null)
                    <tr>
                        <td>{{ $ch ? $ch->numero : $l->numero_ref }}</td>
                        <td>{{ $ch ? $ch->code_banque : ($l->meta['code_banque'] ?? '') }}</td>
                        <td class="text-right">{{ number_format($ch ? $ch->montant : ($l->montant ?? 0), 2, ',', ' ') }}</td>
                        <td>{{ $ch ? $ch->echeance : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
</article>
</body>
</html> --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bordereau d'envoi — {{ $bordereau->numero }}</title>
    <style>
        /* Styles adaptés pour Dompdf */
        html, body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #000; }
        article, header, section, footer { display: block; }
        h1, h3, h4 { margin: 0; }
        p { margin: 2px 0; }
        
        .header { text-align: center; margin-bottom: 20px; }
        .header img { max-height: 60px; margin-bottom: 5px; }
        .header h1 { font-size: 18px; text-transform: uppercase; }

        .info { margin-bottom: 15px; }
        .info p { font-size: 12px; }

        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background: #f5f5f5; text-align: center; font-weight: bold; }
        td { vertical-align: top; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .mt-20 { margin-top: 20px; }
        .totaux { margin-top: 10px; font-weight: bold; text-align: right; }

        footer { margin-top: 40px; }
        .signatures { width: 100%; margin-top: 30px; }
        .signatures td { border: none; padding: 20px; vertical-align: bottom; }
        .signatures .sig { border-top: 1px solid #000; text-align: center; }
    </style>
</head>
<body>
<article>
    <!-- HEADER -->
    <header class="header">
        {{-- Logo si disponible --}}
        @if(isset($logo))
            <img src="{{ $logo }}" alt="Logo">
        @endif
        <h1>Bordereau d'envoi</h1>
        <p><strong>N° {{ $bordereau->numero }}</strong></p>
    </header>

    <!-- INFOS GÉNÉRALES -->
    <section class="info">
        <p><strong>Date :</strong> {{ $bordereau->date_envoi }}</p>
        @if($bordereau->note)
            <p><strong>Note :</strong> {{ $bordereau->note }}</p>
        @endif
    </section>

    <!-- CONTRE-BONS -->
    <section class="mt-20">
        <h4>Contre-bons</h4>
        <table>
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Société</th>
                    <th>Livreur</th>
                    <th>Date</th>
                    <th>Montant attendu</th>
                </tr>
            </thead>
            <tbody>
                @php($totalCB = 0)
                @foreach($contreBons as $cb)
                    @php($totalCB += $cb->montant)
                    <tr>
                        <td>{{ $cb->numero }}</td>
                        <td>{{ optional($cb->company)->name }}</td>
                        <td>{{ optional($cb->livreur)->name }}</td>
                        <td class="text-center">{{ $cb->date }}</td>
                        <td class="text-right">{{ number_format($cb->montant, 2, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="totaux">Total contre-bons : {{ number_format($totalCB, 2, ',', ' ') }} DA</p>
    </section>

    <!-- CHÈQUES -->
    <section class="mt-20">
        <h4>Chèques</h4>
        <table>
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Banque</th>
                    <th>Montant</th>
                    <th>Échéance</th>
                </tr>
            </thead>
            <tbody>
                @php($totalChq = 0)
                @foreach($chequeLignes as $l)
                    @php($ch = $l->reference_id ? ($cheques[$l->reference_id] ?? null) : null)
                    @php($montant = $ch ? $ch->montant : ($l->montant ?? 0))
                    @php($totalChq += $montant)
                    <tr>
                        <td>{{ $ch ? $ch->numero : $l->numero_ref }}</td>
                        <td>{{ $ch ? $ch->code_banque : ($l->meta['code_banque'] ?? '') }}</td>
                        <td class="text-right">{{ number_format($montant, 2, ',', ' ') }}</td>
                        <td class="text-center">{{ $ch ? $ch->echeance : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="totaux">Total chèques : {{ number_format($totalChq, 2, ',', ' ') }} DA</p>
    </section>

    <!-- SIGNATURES -->
    <footer>
        <table class="signatures">
            <tr>
                <td class="sig">Signature Expéditeur</td>
                <td class="sig">Signature Réceptionnaire</td>
            </tr>
        </table>
    </footer>
</article>
</body>
</html>
