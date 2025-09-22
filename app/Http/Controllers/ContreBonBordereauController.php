<?php

namespace App\Http\Controllers;

use App\Models\ContreBon;
use App\Models\RecouvrementBon;
use App\Models\Cheque;

class ContreBonBordereauController extends Controller
{
    public function show(ContreBon $contreBon)
    {
        $bons = RecouvrementBon::where('contre_bon_id', $contreBon->id)->get();
        $cheques = Cheque::where('contre_bon_id', $contreBon->id)->get();
        $total_especes = $bons->where('type', 'espece')->sum('montant');
        $total_cheques = $cheques->sum('montant');
        return view('contre_bons.bordereau', compact('contreBon','bons','cheques','total_especes','total_cheques'));
    }
}