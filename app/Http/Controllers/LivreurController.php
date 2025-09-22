<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class LivreurController extends Controller
{
    public function index()
    {
        $items = User::where('role', 'livreur')->orderBy('name')->paginate(20);
        return view('livreurs.index', compact('items'));
    }
    public function create()
    {
        return view('livreurs.create');
    }
    public function store(Request $r)
    {
        $data = $r->validate(['name' => 'required|string', 'email' => 'required|email|unique:users,email']);
        $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => bcrypt(str()->random(12)), 'role' => 'livreur']);
        return redirect()->route('livreurs.index')->with('ok', 'Livreur créé');
    }
    public function edit(User $livreur)
    {
        return view('livreurs.edit', compact('livreur'));
    }
    public function update(Request $r, User $livreur)
    {
        $data = $r->validate(['name' => 'required|string', 'email' => 'required|email|unique:users,email,' . $livreur->id]);
        $livreur->update($data);
        return redirect()->route('livreurs.index')->with('ok', 'Mis à jour');
    }
    public function destroy(User $livreur)
    {
        $livreur->delete();
        return back()->with('ok', 'Supprimé');
    }
}