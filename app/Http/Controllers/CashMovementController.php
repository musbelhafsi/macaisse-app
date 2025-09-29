<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\CashRegister;
use Illuminate\Http\Request;

class CashMovementController extends Controller
{
    public function index(Request $r)
    {
        $user = auth()->user();
        $cashId = $user?->current_cash_id;
        abort_unless($cashId, 403, 'Aucune caisse courante sélectionnée.');

        $query = CashMovement::with('source')
            ->where('cash_id', $cashId)
          ->orderBydesc('id')
            ->orderBy('date_mvt') 
            ;

        if ($r->filled('type')) $query->where('type', $r->type);
        if ($r->filled('from') && $r->filled('to')) $query->whereBetween('date_mvt', [$r->from, $r->to]);

        $items = $query->paginate(50)->appends($r->query());
        $currentCash = CashRegister::find($cashId);
        return view('movements.index', compact('items','currentCash'));
    }
}