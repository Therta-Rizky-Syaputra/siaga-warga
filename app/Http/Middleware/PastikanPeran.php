<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PastikanPeran
{
    /** Pemakaian: ->middleware('peran:pengurus,admin') */
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        abort_unless($request->user() && in_array($request->user()->role, $peran, true), 403);

        return $next($request);
    }
}
