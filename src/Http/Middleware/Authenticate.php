<?php

namespace Aminrafiei\Horizon\Http\Middleware;

use Aminrafiei\Horizon\Horizon;

class Authenticate
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response|null
     */
    public function handle($request, $next)
    {
        return Horizon::check($request) ? $next($request) : abort(403);
    }
}
