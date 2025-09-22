<?php

namespace App\Http\Controllers;

use App\Models\RecouvrementBon;
use App\Models\Company;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;

class RecouvrementBonController extends Controller
{
    public function index()
    {
        $bons = RecouvrementBon::with(['client'])->latest()->paginate(20);
        return view('bons.index', compact('bons'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $livreurs = User::orderBy('name')->get();
        $clients = Client::orderBy('name')->get();
        return view('bons.create', compact('companies', 'livreurs', 'clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'numero' => 'required|string',
            'company_id' => 'required|exists:companies,id',
            'livreur_id' => 'required|exists:users,id',
            'date_recouvrement' => 'required|date',
            'client_id' => 'required|exists:clients,id',
            'montant' => 'required|numeric',
            'type' => 'required|in:espece,cheque',
            'note' => 'nullable|string',
        ]);

        RecouvrementBon::create($data);

        return redirect()->route('bons.index')->with('ok', 'Bon créé.');
    }
}