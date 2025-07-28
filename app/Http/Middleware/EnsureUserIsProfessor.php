<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureUserIsProfessor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!auth()->check()) {
            throw new AccessDeniedHttpException('User needs to be logged in to access this page.');
            // return redirect('/login');
        }

        if(!auth()->user()->isProfessor()) {
            throw new AccessDeniedHttpException('User is not professor.');
        }

        return $next($request);
    }
}
