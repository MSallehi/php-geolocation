<?php

/**
 * WordPress Integration for GeoLocation Package
 * 
 * This file provides WordPress-specific helper functions.
 * Include this file in your WordPress theme or plugin.
 * 
 * Usage:
 * require_once 'path/to/vendor/autoload.php';
 * require_once 'path/to/vendor/msallehi/php-geolocation/src/WordPress/functions.php';
 */

use MSallehi\GeoLocation\GeoLocation;
use MSallehi\GeoLocation\Exceptions\CountryNotAllowedException;

if (!function_exists('geolocation')) {
    /**
     * Get GeoLocation instance
     */
    function geolocation(array $config = []): GeoLocation
    {
        static $instance = null;
        
        if ($instance === null || !empty($config)) {
            $instance = new GeoLocation($config);
        }
        
        return $instance;
    }
}

if (!function_exists('geo_get_country')) {
    /**
     * Get country code from IP
     */
    function geo_get_country(?string $ip = null): ?string
    {
        return geolocation()->getCountryFromIp($ip);
    }
}

if (!function_exists('geo_is_allowed')) {
    /**
     * Check if IP is from allowed countries
     */
    function geo_is_allowed(?string $ip = null): bool
    {
        return geolocation()->isAllowed($ip);
    }
}

if (!function_exists('geo_guard')) {
    /**
     * Block access if not from allowed countries
     */
    function geo_guard(?string $ip = null): void
    {
        geolocation()->guard($ip);
    }
}

if (!function_exists('geo_set_countries')) {
    /**
     * Set allowed countries
     */
    function geo_set_countries(array $countries): GeoLocation
    {
        return geolocation()->setAllowedCountries($countries);
    }
}

if (!function_exists('geo_get_location')) {
    /**
     * Get full location details
     */
    function geo_get_location(?string $ip = null): array
    {
        return geolocation()->getLocationDetails($ip);
    }
}

/**
 * WordPress Shortcode: [geo_restrict country="IR,US"]Content only for Iran and US[/geo_restrict]
 */
if (function_exists('add_shortcode')) {
    add_shortcode('geo_restrict', function ($atts, $content = null) {
        $atts = shortcode_atts([
            'country' => 'IR',
            'message' => 'این محتوا در کشور شما در دسترس نیست.',
        ], $atts);

        $countries = array_map('trim', explode(',', $atts['country']));
        $geo = new GeoLocation(['allowed_countries' => $countries]);

        if ($geo->isAllowed()) {
            return do_shortcode($content);
        }

        return '<div class="geo-restricted">' . esc_html($atts['message']) . '</div>';
    });
}

/**
 * WordPress Action Hook for restriction
 * 
 * Usage in theme:
 * add_action('template_redirect', 'geo_wp_restrict');
 * 
 * Or with custom countries:
 * add_action('template_redirect', function() {
 *     geo_wp_restrict(['IR', 'TR']);
 * });
 */
if (!function_exists('geo_wp_restrict')) {
    function geo_wp_restrict(array $countries = ['IR']): void
    {
        $geo = new GeoLocation(['allowed_countries' => $countries]);
        
        if (!$geo->isAllowed()) {
            wp_die(
                'دسترسی از کشور شما امکان‌پذیر نیست.',
                'دسترسی محدود',
                ['response' => 403]
            );
        }
    }
}
