<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashSelectionController extends Controller
{
    // Show cash selection after login (or to switch)
    public function show()
    {
        $user = Auth::user();
        $cashes = $user->cashes()->orderBy('name')->get();
        return view('auth.select_cash', compact('cashes'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate(['cash_id' => 'required|exists:cash_registers,id']);
        if (!$user->canSwitchToCash((int)$data['cash_id'])) {
            return back()->withErrors(['cash_id' => 'Vous n\'avez pas accès à cette caisse.']);
        }
        $user->current_cash_id = $data['cash_id'];
        $user->save();
        return redirect()->intended('/');
    }
}