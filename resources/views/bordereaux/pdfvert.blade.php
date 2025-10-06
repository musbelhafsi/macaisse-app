<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bordereau d'envoi — {{ $bordereau->numero }}</title>
    <style>
        /* Reset et polices */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { 
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif; 
            font-size: 12px; 
            color: #2d3748; 
            line-height: 1.4;
        }
        
        /* Couleurs émeraude claire */
        :root {
            --emerald-primary: #10b981;
            --emerald-light: #a7f3d0;
            --emerald-dark: #047857;
            --emerald-bg: #f0fdf9;
            --text-dark: #1f2937;
            --text-light: #6b7280;
        }
        
        /* Layout principal */
        .container { max-width: 100%; margin: 0 auto; padding: 20px; }
        
        /* En-tête professionnel - Version émeraude */
        .header { 
            text-align: center; 
            margin-bottom: 25px; 
            padding-bottom: 15px;
            border-bottom: 2px solid var(--emerald-primary);
            background: linear-gradient(135deg, var(--emerald-bg) 0%, #ffffff 100%);
            border-radius: 8px;
            padding: 20px;
        }
        .header img { 
            max-height: 70px; 
            margin-bottom: 10px; 
        }
        .header h1 { 
            font-size: 22px; 
            text-transform: uppercase; 
            color: var(--emerald-dark);
            font-weight: 700;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }
        .header .numero { 
            font-size: 14px; 
            color: var(--emerald-primary); 
            font-weight: 600;
            background: var(--emerald-light);
            padding: 4px 12px;
            border-radius: 20px;
            display: inline-block;
        }
        
        /* Informations générales */
        .info-box {
            background: var(--emerald-bg);
            border: 1px solid var(--emerald-light);
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .info-item {
            margin-bottom: 8px;
            display: flex;
        }
        .info-label {
            font-weight: 600;
            color: var(--emerald-dark);
            min-width: 80px;
        }
        .info-value {
            color: var(--text-dark);
        }
        
        /* Sections */
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section-title {
            background: linear-gradient(135deg, var(--emerald-primary) 0%, var(--emerald-dark) 100%);
            color: white;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
        }
        
        /* Tables améliorées - Version émeraude */
        .table-container {
            overflow: hidden;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.1);
            border: 1px solid var(--emerald-light);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        th {
            background: linear-gradient(135deg, var(--emerald-primary) 0%, var(--emerald-dark) 100%);
            color: white;
            padding: 10px 8px;
            font-weight: 600;
            text-align: left;
            border: none;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid var(--emerald-light);
            vertical-align: middle;
        }
        tr:nth-child(even) {
            background: var(--emerald-bg);
        }
        tr:hover {
            background: #e0f2f1;
        }
        
        /* Alignements */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        
        /* Totaux */
        .total-row {
            background: linear-gradient(135deg, var(--emerald-dark) 0%, #065f46 100%) !important;
            color: white;
            font-weight: 700;
            font-size: 12px;
        }
        .total-label {
            text-align: right;
            padding-right: 15px;
        }
        
        /* Signatures */
        .signatures {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid var(--emerald-light);
        }
        .signature-box {
            display: inline-block;
            width: 45%;
            text-align: center;
            margin: 0 2%;
        }
        .signature-line {
            border-top: 1px solid var(--emerald-primary);
            margin-top: 60px;
            padding-top: 5px;
            font-size: 10px;
            color: var(--emerald-dark);
        }
        
        /* Badges et états */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
        }
        .badge-primary { 
            background: var(--emerald-primary); 
            color: white; 
        }
        .badge-success { 
            background: #22c55e; 
            color: white; 
        }
        .badge-light {
            background: var(--emerald-light);
            color: var(--emerald-dark);
        }
        
        /* Utilitaires */
        .mt-20 { margin-top: 20px; }
        .mb-10 { margin-bottom: 10px; }
        .page-break { page-break-before: always; }
        
        /* Pied de page */
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid var(--emerald-light);
            text-align: center;
            font-size: 10px;
            color: var(--emerald-primary);
        }
        
        /* Récapitulatif */
        .recap-box {
            background: linear-gradient(135deg, var(--emerald-bg) 0%, #ffffff 100%);
            border: 1px solid var(--emerald-light);
            border-radius: 8px;
            padding: 20px;
        }
        .recap-item {
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px dashed var(--emerald-light);
        }
        .recap-total {
            background: var(--emerald-primary);
            color: white;
            padding: 12px;
            border-radius: 6px;
            font-weight: 700;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER PROFESSIONNEL - Émeraude -->
        <header class="header">
            @if(isset($logo))
                <img src="{{ $logo }}" alt="Logo">
            @endif
            <h1>BORDEREAU D'ENVOI</h1>
            <p class="numero">Référence : {{ $bordereau->numero }}</p>
        </header>

        <!-- INFORMATIONS GÉNÉRALES -->
        <div class="info-box">
            <div class="info-item">
                <span class="info-label">Date :</span>
                <span class="info-value">{{ $bordereau->date_envoi }}</span>
            </div>
            @if($bordereau->note)
            <div class="info-item">
                <span class="info-label">Note :</span>
                <span class="info-value">{{ $bordereau->note }}</span>
            </div>
            @endif
            <div class="info-item">
                <span class="info-label">Statut :</span>
                <span class="badge badge-primary">ENVOYÉ</span>
            </div>
        </div>

        <!-- CONTRE-BONS -->
        <section class="section">
            <div class="section-title">CONTRE-BONS</div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="15%">N° Contre-bon</th>
                            <th width="25%">Société</th>
                            <th width="20%">Livreur</th>
                            <th width="15%" class="text-center">Date</th>
                            <th width="15%" class="text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php($totalCB = 0)
                        @foreach($contreBons as $cb)
                            @php($totalCB += $cb->montant)
                            <tr>
                                <td><strong>#{{ $cb->numero }}</strong></td>
                                <td>{{ optional($cb->company)->name ?? 'N/A' }}</td>
                                <td>{{ optional($cb->livreur)->name ?? 'N/A' }}</td>
                                <td class="text-center">{{ $cb->date }}</td>
                                <td class="text-right">{{ number_format($cb->montant, 2, ',', ' ') }} DA</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="4" class="total-label">TOTAL CONTRE-BONS :</td>
                            <td class="text-right">{{ number_format($totalCB, 2, ',', ' ') }} DA</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        <!-- CHÈQUES -->
        <section class="section">
            <div class="section-title">CHÈQUES</div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="20%">N° Chèque</th>
                            <th width="25%">Banque</th>
                            <th width="20%" class="text-right">Montant</th>
                            <th width="20%" class="text-center">Échéance</th>
                            <th width="15%" class="text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php($totalChq = 0)
                        @foreach($chequeLignes as $l)
                            @php($ch = $l->reference_id ? ($cheques[$l->reference_id] ?? null) : null)
                            @php($montant = $ch ? $ch->montant : ($l->montant ?? 0))
                            @php($totalChq += $montant)
                            <tr>
                                <td><strong>{{ $ch ? $ch->numero : $l->numero_ref }}</strong></td>
                                <td>{{ $ch ? $ch->code_banque : ($l->meta['code_banque'] ?? 'N/A') }}</td>
                                <td class="text-right">{{ number_format($montant, 2, ',', ' ') }} DA</td>
                                <td class="text-center">{{ $ch ? $ch->echeance : 'N/A' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-success">ACTIF</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="2" class="total-label">TOTAL CHÈQUES :</td>
                            <td class="text-right">{{ number_format($totalChq, 2, ',', ' ') }} DA</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        <!-- RÉCAPITULATIF - Version émeraude -->
        <section class="section">
            <div class="section-title">RÉCAPITULATIF</div>
            <div class="recap-box">
                <div class="recap-item">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-weight: 600; color: var(--emerald-dark);">Total Contre-bons :</span>
                        <span style="font-weight: 600;">{{ number_format($totalCB, 2, ',', ' ') }} DA</span>
                    </div>
                </div>
                <div class="recap-item">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-weight: 600; color: var(--emerald-dark);">Total Chèques :</span>
                        <span style="font-weight: 600;">{{ number_format($totalChq, 2, ',', ' ') }} DA</span>
                    </div>
                </div>
                <div class="recap-total">
                    <div style="display: flex; justify-content: space-between;">
                        <span>TOTAL GÉNÉRAL :</span>
                        <span>{{ number_format($totalCB + $totalChq, 2, ',', ' ') }} DA</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- SIGNATURES -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div style="color: var(--emerald-dark); font-weight: 600;">Signature Expéditeur</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div style="color: var(--emerald-dark); font-weight: 600;">Signature Réceptionnaire</div>
            </div>
        </div>

        <!-- PIED DE PAGE -->
        <div class="footer">
            Document généré le {{ date('d/m/Y à H:i') }} • Page 1/1
        </div>
    </div>
</body>
</html>
