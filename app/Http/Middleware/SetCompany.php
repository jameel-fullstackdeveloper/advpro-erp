<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('company_id')) {
            session(['company_id' => $request->company_id]);
        }

        if (!session('company_id')) {
            // Optionally, redirect to a company selection page
            return redirect()->route('company.select');
        }

        return $next($request);
    }
}
