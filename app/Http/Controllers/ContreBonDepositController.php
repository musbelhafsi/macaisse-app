<?php

namespace App\Http\Controllers;

use App\Models\ContreBon;
use App\Models\CashRegister;
use App\Models\CashMovement;
use App\Models\RecouvrementBon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContreBonDepositController extends Controller
{
    // Form pour déposer les espèces d'un contre-bon
    public function create(ContreBon $contreBon)
    {
        $caisses = CashRegister::orderBy('name')->get();
        // Montant espèces = somme des bons en espèce (exclut chèques)
        $montant_especes = RecouvrementBon::where('contre_bon_id', $contreBon->id)
            ->where('type', 'espece')
            ->sum('montant');
        return view('contre_bons.deposit', compact('contreBon','caisses','montant_especes'));
    }

    // Enregistre le dépôt espèces sur une caisse
    public function store(Request $request, ContreBon $contreBon)
    {
        $data = $request->validate([
            'cash_id' => 'required|exists:cash_registers,id',
            'montant' => 'required|numeric|min:0.01',
        ]);

        return DB::transaction(function () use ($contreBon, $data) {
            // Crédit de la caisse: recette
            CashMovement::create([
                'cash_id' => $data['cash_id'],
                'type' => 'recette',
                'montant' => $data['montant'],
                'source_type' => ContreBon::class,
                'source_id' => $contreBon->id,
                'description' => 'Dépôt espèces contre‑bon #' . $contreBon->numero,
                'date_mvt' => Carbon::parse($contreBon->date),
            ]);

            // Ecart = total espèces attendues - déposé
            $total_especes = RecouvrementBon::where('contre_bon_id', $contreBon->id)
                ->where('type', 'espece')
                ->sum('montant');
            $contreBon->ecart = round($data['montant'] - $total_especes, 2);
            $contreBon->save();

            return redirect()->route('contre-bons.show', $contreBon)->with('ok', 'Dépôt espèces enregistré.');
        });
    }
}