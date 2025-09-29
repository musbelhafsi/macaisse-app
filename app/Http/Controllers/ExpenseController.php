<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\CashMovement;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::orderByDesc('numero')->paginate(20);
        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {
        $caisses = CashRegister::orderBy('name')->get();
        return view('expenses.create', compact('caisses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cash_id' => 'required|exists:cash_registers,id',
            'date' => 'required|date',
            'numero' => 'required|string',
            'libelle' => 'required|string',
            'montant' => 'required|numeric|min:0.01',
            'note' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($data) {
            $expense = Expense::create($data);

            CashMovement::create([
                'cash_id' => $expense->cash_id,
                'type' => 'depense',
                'montant' => $expense->montant,
                'source_type' => Expense::class,
                'source_id' => $expense->id,
                'description' => $expense->libelle,
                'date_mvt' => Carbon::parse($expense->date),
            ]);

            return redirect()->route('expenses.index')->with('ok', 'Dépense enregistrée.');
        });
    }
}