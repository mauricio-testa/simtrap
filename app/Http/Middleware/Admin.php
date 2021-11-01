<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (Auth::user()->level != config('constants.LEVEL_ROOT')) {
            return response('Não permitido. <a href="/"> Voltar</a> ', 403);
        }

        return $next($request);
    }
}
