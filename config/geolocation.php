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
    | Supported: 'ip-api', 'ipinfo', 'ipdata'
    |
    | 'ip-api' - Free, no API key required (http://ip-api.com)
    | 'ipinfo' - Free tier available, API key optional (https://ipinfo.io)
    | 'ipdata' - Requires API key (https://ipdata.co)
    |
    */
    'api_provider' => 'ip-api',

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

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | The maximum time (in seconds) to wait for API response.
    |
    */
    'timeout' => 5,

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
