<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasDiscipline
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isVotingUser() && ! $user->discipline_id) {
            abort(403, 'Access denied: You have not been assigned to a discipline. Please contact the administrator.');
        }

        return $next($request);
    }
}
