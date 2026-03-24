<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPublicationOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $publication = $request->route('publication');
        
        if (!$publication) {
            return $next($request);
        }
        
        $user = Auth::user();
        
        // Pi e manager possono editare/eliminare tutto
        if (in_array($user->role, ['pi', 'manager'])) {
            return $next($request);
        }
        
        // Researcher può editare/eliminare solo le sue pubblicazioni
        if ($user->role === 'researcher') {
            $isAuthor = $publication->authors()
                ->where('user_id', $user->id)
                ->exists();
            
            if (!$isAuthor) {
                abort(403, 'Non sei autore di questa pubblicazione');
            }
        }
        
        return $next($request);
    }
}
