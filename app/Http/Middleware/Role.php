<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class Role
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        /* $user = Auth::user();
        if (!$user || $user->role !== $role) {
            abort(403);
        }
        return $next($request); */
        
         $user = Auth::user();

        if (!$user) {
            abort(403, 'Non authentifié.');
        }

        // Autoriser plusieurs rôles séparés par virgule ou barre verticale
        $allowedRoles = preg_split('/[,\|]/', $roles);

        if (!in_array($user->role, $allowedRoles)) {
            abort(403, 'Accès refusé.');
        }

        return $next($request);
    }
}