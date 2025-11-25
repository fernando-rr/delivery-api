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
     * Suffix used to identify tenant subdomains.
     * Example: "demo-delivery.domain.com" -> slug = "demo"
     */
    private const TENANT_SUFFIX = '-delivery';

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $slug = $this->extractSlugFromHost($host);

        $restaurant = Restaurant::query()
            ->where('domain', $host)
            ->orWhere('slug', $slug)
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

    /**
     * Extract tenant slug from host.
     *
     * Supports two formats:
     * 1. "{slug}-delivery.domain.com" -> extracts "slug"
     * 2. "{slug}.domain.com" -> extracts "slug" (fallback for custom domains)
     */
    private function extractSlugFromHost(string $host): ?string
    {
        $subdomain = explode('.', $host)[0];

        // Check if subdomain ends with tenant suffix (e.g., "demo-delivery")
        if (str_ends_with($subdomain, self::TENANT_SUFFIX)) {
            return substr($subdomain, 0, -strlen(self::TENANT_SUFFIX));
        }

        // Fallback: use subdomain as-is (for custom domains like "pizzaria.com")
        return $subdomain;
    }
}
