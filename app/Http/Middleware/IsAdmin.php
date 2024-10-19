<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && (Auth::user()->roles == 'admin' || Auth::user()->roles == 'root')) //untuk membuat autentikasi hanya admin dan root yang dapat masuk kelaman login
        {
            return $next($request);
        } else{
            return abort(403);
        }
        
    }
}
