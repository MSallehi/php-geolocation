<?php

/**
 * Laravel Middleware for GeoLocation Package
 * 
 * This file is optional and only needed for Laravel projects.
 */

namespace MSallehi\GeoLocation\Laravel;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use MSallehi\GeoLocation\GeoLocation;
use MSallehi\GeoLocation\Exceptions\CountryNotAllowedException;

class GeoLocationMiddleware
{
    protected GeoLocation $geoLocation;

    public function __construct(GeoLocation $geoLocation)
    {
        $this->geoLocation = $geoLocation;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string ...$countries): Response
    {
        // If countries are passed as middleware parameters, use them
        if (!empty($countries)) {
            $this->geoLocation->setAllowedCountries($countries);
        }

        try {
            $this->geoLocation->validate($request->ip());
        } catch (CountryNotAllowedException $e) {
            return $this->handleNotAllowed($request, $e);
        }

        return $next($request);
    }

    /**
     * Handle not allowed response
     */
    protected function handleNotAllowed(Request $request, CountryNotAllowedException $e): Response
    {
        $config = $this->geoLocation->getConfig();
        $statusCode = $config['response']['status_code'] ?? 403;

        if ($request->expectsJson()) {
            return response()->json($e->toArray(), $statusCode);
        }

        return response($e->getMessage(), $statusCode);
    }
}
