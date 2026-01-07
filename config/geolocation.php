<?php

/**
 * GeoLocation Package Configuration
 * 
 * This is an example configuration file.
 * For pure PHP: Pass this array directly to the GeoLocation constructor.
 * For Laravel: Publish this file to config/geolocation.php
 * For WordPress: Use the config array with geolocation() function.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Allowed Countries
    |--------------------------------------------------------------------------
    |
    | List of country codes that are allowed to access. Use ISO 3166-1 alpha-2
    | country codes (e.g., 'IR' for Iran, 'US' for United States).
    |
    */
    'allowed_countries' => ['IR'],

    /*
    |--------------------------------------------------------------------------
    | API Provider
    |--------------------------------------------------------------------------
    |
    | The geolocation API provider to use for IP lookup.
    | Supported: 'ip-api', 'ip-api-ir', 'ipinfo', 'ipdata'
    |
    | 'ip-api'    - Free, no API key required (http://ip-api.com)
    | 'ip-api-ir' - Iranian GeoIP API, optimized for Iranian servers (https://ip-api.ir)
    | 'ipinfo'    - Free tier available, API key optional (https://ipinfo.io)
    | 'ipdata'    - Requires API key (https://ipdata.co)
    |
    */
    'api_provider' => 'ip-api',

    /*
    |--------------------------------------------------------------------------
    | Fallback Providers
    |--------------------------------------------------------------------------
    |
    | List of providers to try if the primary provider fails.
    | The package will try each provider in order until one succeeds.
    |
    */
    'fallback_providers' => ['ip-api', 'ipinfo'],

    /*
    |--------------------------------------------------------------------------
    | API Keys
    |--------------------------------------------------------------------------
    |
    | API keys for different providers (if required).
    |
    */
    'ipinfo_token' => '',
    'ipdata_api_key' => '',
    'ip_api_ir_guid' => '', // Optional GUID for ip-api.ir

    /*
    |--------------------------------------------------------------------------
    | Timeout Settings
    |--------------------------------------------------------------------------
    |
    | The maximum time (in seconds) to wait for API response.
    |
    */
    'timeout' => 5,
    'connect_timeout' => 3,

    /*
    |--------------------------------------------------------------------------
    | Retry Settings
    |--------------------------------------------------------------------------
    |
    | Number of times to retry API calls on failure.
    |
    */
    'retry_count' => 2,

    /*
    |--------------------------------------------------------------------------
    | Fallback Allow
    |--------------------------------------------------------------------------
    |
    | If true, users will be ALLOWED access when API fails.
    | If false, users will be DENIED access when API fails.
    | 
    | Recommended: true (don't block legitimate users due to API issues)
    |
    */
    'fallback_allow' => true,

    /*
    |--------------------------------------------------------------------------
    | Local IP Handling
    |--------------------------------------------------------------------------
    |
    | How to handle local/private IP addresses (127.0.0.1, 192.168.x.x, etc.)
    |
    */
    'allow_local' => true,
    'local_country' => 'LOCAL',

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Enable caching for IP lookups to improve performance.
    |
    */
    'cache_enabled' => true,
    'cache_ttl' => 3600, // 1 hour

    /*
    |--------------------------------------------------------------------------
    | Error Messages
    |--------------------------------------------------------------------------
    |
    | Custom error messages for different scenarios.
    | You can customize these messages as needed.
    |
    */
    'messages' => [
        'not_allowed' => 'دسترسی از کشور شما امکان‌پذیر نیست.',
        'api_error' => 'امکان تشخیص موقعیت شما وجود ندارد.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Settings (for automatic deny)
    |--------------------------------------------------------------------------
    */
    'response' => [
        'status_code' => 403,
        'json_response' => true,
    ],
];
