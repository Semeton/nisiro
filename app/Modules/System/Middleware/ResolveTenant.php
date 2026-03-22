<?php

declare(strict_types=1);

namespace Modules\System\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\System\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantIdentifier = $request->header('X-Tenant-ID')
            ?? $request->getHost();

        // Simple resolution logic for now: subdomain or exact domain.
        // We can refine this later (e.g., stripping subdomains, etc.)
        $tenant = Tenant::where('domain', $tenantIdentifier)
            ->orWhere('subdomain', explode('.', $tenantIdentifier)[0])
            ->first();

        if ($tenant) {
            app()->instance('currentTenant', $tenant);
        }

        return $next($request);
    }
}
