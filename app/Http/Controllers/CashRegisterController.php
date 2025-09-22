<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\User;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    public function index()
    {
        $items = CashRegister::orderBy('name')->paginate(20);

        return view('cash_registers.index', compact('items'));
    }

    public function create()
    {
        return view('cash_registers.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate(['name' => 'required|string', 'is_main' => 'nullable|boolean', 'currency' => 'required|string|size:2|in:DA,EU,DT,USD,EUR', 'balance' => 'nullable|numeric']);
        $data['is_main'] = (bool) ($data['is_main'] ?? false);
        $data['balance'] = $data['balance'] ?? 0;
        CashRegister::create($data);

        return redirect()->route('cash-registers.index')->with('ok', 'Caisse créée');
    }

    public function show(CashRegister $cash_register)
    {
        $cash_register->load('users');
        // users non encore attachés pour le formulaire d'ajout
        $attachedIds = $cash_register->users->pluck('id');
        $availableUsers = User::orderBy('name')
            ->when($attachedIds->isNotEmpty(), fn($q) => $q->whereNotIn('id', $attachedIds))
            ->get();

        return view('cash_registers.show', [
            'cashRegister' => $cash_register,
            'availableUsers' => $availableUsers,
        ]);
    }

    // Attacher un utilisateur à la caisse
    public function attachUser(Request $r, CashRegister $cash_register)
    {
        $data = $r->validate(['user_id' => 'required|exists:users,id']);
        if (!$cash_register->users()->where('user_id', $data['user_id'])->exists()) {
            $cash_register->users()->attach($data['user_id']);
        }
        return back()->with('ok', 'Accès accordé');
    }

    // Détacher un utilisateur de la caisse
    public function detachUser(CashRegister $cash_register, User $user)
    {
        $cash_register->users()->detach($user->id);
        return back()->with('ok', 'Accès retiré');
    }

    public function edit(CashRegister $cash_register)
    {
        return view('cash_registers.edit', ['cashRegister' => $cash_register]);
    }

    public function update(Request $r, CashRegister $cash_register)
    {
        $data = $r->validate(['name' => 'required|string', 'is_main' => 'nullable|boolean', 'currency' => 'required|string|size:2|in:DA,EU,DT,USD,EUR', 'balance' => 'nullable|numeric']);
        $data['is_main'] = (bool) ($data['is_main'] ?? false);
        $cash_register->update($data);

        return redirect()->route('cash-registers.index')->with('ok', 'Mis à jour');
    }

    public function destroy(CashRegister $cash_register)
    {
        $cash_register->delete();

        return back()->with('ok', 'Supprimé');
    }
}
