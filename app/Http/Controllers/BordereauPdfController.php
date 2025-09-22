<?php

namespace App\Http\Controllers;

use App\Models\ContreBon;
use App\Models\RecouvrementBon;
use App\Models\Cheque;
use Barryvdh\DomPDF\Facade\Pdf;

class BordereauPdfController extends Controller
{
    public function show(ContreBon $contreBon)
    {
        $bons = RecouvrementBon::where('contre_bon_id', $contreBon->id)->get();
        $cheques = Cheque::where('contre_bon_id', $contreBon->id)->get();
        $total_especes = $bons->where('type', 'espece')->sum('montant');
        $total_cheques = $cheques->sum('montant');

        $pdf = Pdf::loadView('contre_bons.bordereau', [
            'contreBon' => $contreBon,
            'bons' => $bons,
            'cheques' => $cheques,
            'total_especes' => $total_especes,
            'total_cheques' => $total_cheques,
        ])->setPaper('A4');

        return $pdf->download('bordereau_'.$contreBon->numero.'.pdf');
    }
}