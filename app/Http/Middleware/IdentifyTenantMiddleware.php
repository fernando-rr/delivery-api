<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Try to find by full domain or subdomain (slug)
        // Assuming subdomain is the first part of the host if it's a subdomain of the main app
        // Ideally we would have a configured central domain to strip out, but for now simplistic approach

        $subdomain = explode('.', $host)[0];

        $restaurant = Restaurant::query()
            ->where('domain', $host)
            ->orWhere('slug', $subdomain)
            ->first();

        if (!$restaurant || !$restaurant->active) {
            return response()->json(['message' => 'Tenant not found or inactive'], 404);
        }

        // Configure tenant connection
        Config::set('database.connections.tenant.database', $restaurant->db_name);
        DB::purge('tenant');
        DB::reconnect('tenant');

        // Set tenant in request for easy access if needed
        $request->merge(['tenant' => $restaurant]);

        return $next($request);
    }
}
