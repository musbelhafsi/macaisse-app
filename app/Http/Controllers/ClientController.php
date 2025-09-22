<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index() { $items = Client::orderBy('name')->paginate(20); return view('clients.index', compact('items')); }
    public function create() { return view('clients.create'); }
    public function store(Request $r) { $data = $r->validate(['name'=>'required|string','code'=>'nullable|string']); Client::create($data); return redirect()->route('clients.index')->with('ok','Client créé'); }
    public function edit(Client $client) { return view('clients.edit', compact('client')); }
    public function update(Request $r, Client $client) { $data = $r->validate(['name'=>'required|string','code'=>'nullable|string']); $client->update($data); return redirect()->route('clients.index')->with('ok','Mis à jour'); }
    public function destroy(Client $client) { $client->delete(); return back()->with('ok','Supprimé'); }
}