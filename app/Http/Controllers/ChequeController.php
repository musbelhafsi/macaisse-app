<?php

namespace App\Http\Controllers;

use App\Models\Cheque;
use App\Models\Company;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;

class ChequeController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $cheques = $query->paginate(20)->withQueryString();

        $companies = Company::orderBy('name')->get();
        $livreurs = User::orderBy('name')->get();
        $clients = Client::orderBy('name')->get();

        return view('cheques.index', compact('cheques', 'companies', 'livreurs', 'clients'));
    }

    /**
     * Construit la requête avec les mêmes filtres utilisés par l'index.
     */
    private function buildFilteredQuery(Request $request)
    {
        $query = Cheque::query()->latest();

        // Text filters
        if ($request->filled('code_banque')) {
            $query->where('code_banque', 'like', "%{$request->string('code_banque')->trim()}%");
        }
        if ($request->filled('numero')) {
            $query->where('numero', 'like', "%{$request->string('numero')->trim()}%");
        }

        // Relations
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->integer('company_id'));
        }
        if ($request->filled('livreur_id')) {
            $query->where('livreur_id', $request->integer('livreur_id'));
        }

        // Dates (date_recouvrement)
        if ($request->filled('date_recouvrement_from') && $request->filled('date_recouvrement_to')) {
            $query->whereBetween('date_recouvrement', [
                $request->date('date_recouvrement_from'),
                $request->date('date_recouvrement_to'),
            ]);
        } else {
            if ($request->filled('date_recouvrement_from')) {
                $query->whereDate('date_recouvrement', '>=', $request->date('date_recouvrement_from'));
            }
            if ($request->filled('date_recouvrement_to')) {
                $query->whereDate('date_recouvrement', '<=', $request->date('date_recouvrement_to'));
            }
        }

        return $query;
    }

    /**
     * Export CSV (compatible Excel) des chèques filtrés (ou non).
     */
    public function export(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $rows = $query->with(['client','livreur','company'])->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="cheques-export-'.now()->format('Ymd_His').'.csv"',
        ];

        $callback = function() use ($rows) {
            $output = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($output, [
                'ID','Date recouvrement','Banque','Numéro','Client','Livreur','Société','Montant','Statut'
            ], ';');

            foreach ($rows as $ch) {
                fputcsv($output, [
                    $ch->id,
                    $ch->date_recouvrement ? \Carbon\Carbon::parse($ch->date_recouvrement)->format('d/m/Y') : '',
                    $ch->code_banque,
                    $ch->numero,
                    optional($ch->client)->name,
                    optional($ch->livreur)->name,
                    optional($ch->company)->name,
                    number_format((float)$ch->montant, 2, ',', ' '),
                    $ch->statut,
                ], ';');
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $livreurs = User::orderBy('name')->get();
        $clients = Client::orderBy('name')->get();
        return view('cheques.create', compact('companies', 'livreurs', 'clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code_banque' => 'required|string',
            'numero' => 'required|string',
            'client_id' => 'required|exists:clients,id',
            'company_id' => 'required|exists:companies,id',
            'livreur_id' => 'required|exists:users,id',
            'montant' => 'required|numeric|min:0.01',
            'echeance' => 'nullable|date',
            'date_recouvrement' => 'nullable|date',
        ]);

        Cheque::create($data);

        return redirect()->route('cheques.index')->with('ok', 'Chèque enregistré en portefeuille.');
    }
    public function edit(Cheque $cheque)
    {
        $companies = Company::orderBy('name')->get();
        $livreurs = User::orderBy('name')->get();
        $clients = Client::orderBy('name')->get();
        return view('cheques.edit', compact('cheque', 'companies', 'livreurs', 'clients'));
    }
    public function update(Request $request, Cheque $cheque)
    {
        $data = $request->validate([
            'code_banque' => 'required|string',
            'numero' => 'required|string',
            'client_id' => 'required|exists:clients,id',
            'company_id' => 'required|exists:companies,id',
            'livreur_id' => 'required|exists:users,id',
            'montant' => 'required|numeric|min:0.01',
            'echeance' => 'nullable|date',
            'date_recouvrement' => 'nullable|date',
        ]);

        $cheque->update($data);

        return redirect()->route('cheques.index')->with('ok', 'Chèque mis à jour.');
    }

    public function show(Cheque $cheque)
    {
        return view('cheques.show', compact('cheque'));
    }

    public function destroy(Cheque $cheque)
    {
        $cheque->delete();
        return redirect()->route('cheques.index')->with('ok', 'Chèque supprimé.');
    }
}