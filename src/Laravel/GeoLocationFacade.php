<?php

/**
 * Laravel Facade for GeoLocation Package
 * 
 * This file is optional and only needed for Laravel projects.
 */

namespace MSallehi\GeoLocation\Laravel;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string|null getCountryFromIp(?string $ip = null)
 * @method static bool isAllowed(?string $ip = null)
 * @method static void validate(?string $ip = null)
 * @method static array getLocationDetails(?string $ip = null)
 * @method static string getClientIp()
 * @method static \MSallehi\GeoLocation\GeoLocation setAllowedCountries(array $countries)
 * @method static \MSallehi\GeoLocation\GeoLocation addAllowedCountry(string $country)
 * @method static \MSallehi\GeoLocation\GeoLocation setMessage(string $key, string $message)
 * @method static array getConfig()
 * @method static void guard(?string $ip = null)
 *
 * @see \MSallehi\GeoLocation\GeoLocation
 */
class GeoLocationFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'geolocation';
    }
}
