<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        // on démarre par récupérer tous les clients
        $query=Client::query()->orderBy('name');
        // si on a un filtre de recherche
        if(request()->filled('search')){
            // on ajoute une clause where
            $search=request()->string('search')->trim();
            $query->where('name','like',"%$search%")
                  ->orWhere('code','like',"%$search%");
        }
 // 🔎 Tri (optionnel)
        if (request()->filled('sort_by')) {
            $sort = request()->get('sort_by');
            $query->orderBy($sort, 'desc'); // tu peux mettre 'asc' si besoin
        } else {
            // Tri par défaut
            $query->orderBy('id', 'desc');
        }
        // on pagine les résultats
        $items=$query->paginate(20)->withQueryString();
        // on retourne la vue avec les clients
        return view('clients.index',compact('items'));

       /*  $items = Client::orderBy('name')->paginate(20);
        return view('clients.index', compact('items')); */
    }
    public function create()
    {
        return view('clients.create');
    }
    public function store(Request $r)
    {
        $data = $r->validate(['name' => 'required|string', 'code' => 'nullable|string']);
        Client::create($data);
        return redirect()->route('clients.index')->with('ok', 'Client créé');
    }
    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }
    public function update(Request $r, Client $client)
    {
        $data = $r->validate(['name' => 'required|string', 'code' => 'nullable|string']);
        $client->update($data);
        return redirect()->route('clients.index')->with('ok', 'Mis à jour');
    }
    /* public function destroy(Client $client)
    {
        try {
            $client->cheques()->count();
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('ko', 'Impossible de supprimer ce client car il est lié à des chèques.');
        }   
        $client->delete();
        return back()->with('ok', 'Supprimé');
    } */
   public function destroy(Client $client)
{
    // Vérification : le client a-t-il des chèques ?
    if ($client->cheques()->exists()) {
        return back()->with('ko', 'Impossible de supprimer ce client car il est lié à des chèques.');
    }

    // Vérification : le client a-t-il des bons de recouvrement ?
    if ($client->recouvrementbons()->exists()) {
        return back()->with('ko', 'Impossible de supprimer ce client car il est lié à des bons de recouvrement.');
    }

    try {
        $client->delete();
        return back()->with('ok', 'Client supprimé avec succès.');
    } catch (\Illuminate\Database\QueryException $e) {
        if ($e->getCode() == '23000') { // violation contrainte FK
            return back()->with('ko', 'Impossible de supprimer ce client car il est encore lié à des données.');
        }
        throw $e; // autre erreur SQL
    }
}

}
