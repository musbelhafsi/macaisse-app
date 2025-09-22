<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Models\CashRegister;
use App\Models\CashMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransferController extends Controller
{
    public function index()
    {
        $transfers = Transfer::latest()->paginate(20);
        return view('transfers.index', compact('transfers'));
    }

    public function create()
    {
        $caisses = CashRegister::orderBy('name')->get();
        return view('transfers.create', compact('caisses'));
    }

    // Emission: débite directement la caisse source
    public function store(Request $request)
    {
        $data = $request->validate([
            'numero' => 'required|string',
            'from_cash_id' => 'required|exists:cash_registers,id',
            'to_cash_id' => 'required|exists:cash_registers,id|different:from_cash_id',
            'montant' => 'required|numeric|min:0.01',
            'note' => 'nullable|string',
            'immediate' => 'nullable|boolean',
        ]);

        $immediate = (bool)($data['immediate'] ?? false);

        return DB::transaction(function () use ($data, $immediate) {
            $transfer = Transfer::create([
                'numero' => $data['numero'],
                'from_cash_id' => $data['from_cash_id'],
                'to_cash_id' => $data['to_cash_id'],
                'montant' => $data['montant'],
                'statut' => $immediate ? 'valide' : 'emis',
                'emitted_at' => Carbon::now(),
                'validated_at' => $immediate ? Carbon::now() : null,
                'montant_recu' => $immediate ? $data['montant'] : null,
                'ecart' => $immediate ? 0 : 0,
                'note' => $data['note'] ?? null,
            ]);

            // Mouvement débit sur la caisse émettrice
            CashMovement::create([
                'cash_id' => $transfer->from_cash_id,
                'type' => 'transfert_debit',
                'montant' => $transfer->montant,
                'source_type' => Transfer::class,
                'source_id' => $transfer->id,
                'description' => 'Emission transfert #' . $transfer->numero,
                'date_mvt' => Carbon::now(),
            ]);

            if ($immediate) {
                // Crédit immédiat de la caisse receptrice
                CashMovement::create([
                    'cash_id' => $transfer->to_cash_id,
                    'type' => 'transfert_credit',
                    'montant' => $transfer->montant,
                    'source_type' => Transfer::class,
                    'source_id' => $transfer->id,
                    'description' => 'Validation immédiate transfert #' . $transfer->numero,
                    'date_mvt' => Carbon::now(),
                ]);
            }

            return redirect()->route('transfers.index')->with('ok', $immediate ? 'Transfert émis et validé.' : 'Transfert émis.');
        });
    }

    public function validateReception(Request $request, Transfer $transfer)
    {
        $data = $request->validate([
            'montant_recu' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($transfer, $data) {
            $transfer->montant_recu = $data['montant_recu'];
            $transfer->ecart = ($transfer->montant_recu - $transfer->montant);
            $transfer->statut = 'valide';
            $transfer->validated_at = Carbon::now();
            $transfer->save();

            // Crédit de la caisse receptrice
            CashMovement::create([
                'cash_id' => $transfer->to_cash_id,
                'type' => 'transfert_credit',
                'montant' => $transfer->montant_recu,
                'source_type' => Transfer::class,
                'source_id' => $transfer->id,
                'description' => 'Validation transfert #' . $transfer->numero,
                'date_mvt' => Carbon::now(),
            ]);

            // Si écart != 0 => ajustement (débit si écart>0 pour corriger caisse émettrice, sinon crédit)
            if ($transfer->ecart != 0) {
                CashMovement::create([
                    'cash_id' => $transfer->from_cash_id,
                    'type' => 'ajustement',
                    'montant' => abs($transfer->ecart),
                    'source_type' => Transfer::class,
                    'source_id' => $transfer->id,
                    'description' => 'Ajustement écart transfert #' . $transfer->numero . ' (écart: ' . $transfer->ecart . ')',
                    'date_mvt' => Carbon::now(),
                ]);
            }

            return redirect()->route('transfers.index')->with('ok', 'Transfert validé.');
        });
    }
}