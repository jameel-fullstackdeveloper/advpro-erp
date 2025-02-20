<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\IpWhitelist;
use Symfony\Component\HttpFoundation\Response;

class RestrictIP
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */



    public function handle(Request $request, Closure $next): Response
    {

        return $next($request);


         /* $allowedIps = IpWhitelist::pluck('ip_address')->toArray();  // Retrieve IPs as an array
            if (!in_array($request->ip(), $allowedIps)) {
                    abort(Response::HTTP_FORBIDDEN, 'Your IP is not allowed.Contact to Administrator');
                }

        return $next($request); */



    }
}
