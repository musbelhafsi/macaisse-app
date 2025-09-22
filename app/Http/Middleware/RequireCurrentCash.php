<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RequireCurrentCash
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if ($user && !$user->current_cash_id && !$request->is('select-cash*')) {
            return redirect()->route('auth.select-cash');
        }
       
       
        return $next($request);
    }
}