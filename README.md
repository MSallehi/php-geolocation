# PHP GeoLocation - IP/Country Restriction Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/msallehi/php-geolocation.svg?style=flat-square)](https://packagist.org/packages/msallehi/php-geolocation)
[![Total Downloads](https://img.shields.io/packagist/dt/msallehi/php-geolocation.svg?style=flat-square)](https://packagist.org/packages/msallehi/php-geolocation)
[![License](https://img.shields.io/packagist/l/msallehi/php-geolocation.svg?style=flat-square)](https://packagist.org/packages/msallehi/php-geolocation)
[![PHP Version](https://img.shields.io/packagist/php-v/msallehi/php-geolocation.svg?style=flat-square)](https://packagist.org/packages/msallehi/php-geolocation)

A PHP package to restrict user access based on their country/IP location. **Works with pure PHP, Laravel, WordPress, and any PHP framework.**

<p align="center">
  <a href="./README.fa.md">🇮🇷 مستندات فارسی</a> |
  <a href="./CHANGELOG.md">📋 Changelog</a>
</p>

## ✨ Features

- 🌍 Automatic country detection from IP address
- 🔒 Restrict access to specific countries
- ⚙️ Fully customizable configuration
- 🔧 Multiple API provider support
- 📝 Custom error messages
- 🔄 Automatic fallback when API fails (v1.1.0+)
- 🎯 Compatible with pure PHP, Laravel, and WordPress

## 📦 Installation

```bash
composer require msallehi/php-geolocation
```

## 🚀 Quick Start

### Pure PHP

```php
<?php
require 'vendor/autoload.php';

use MSallehi\GeoLocation\GeoLocation;

// Create instance with default settings (Iran only)
$geo = new GeoLocation();

// Or with custom settings
$geo = new GeoLocation([
    'allowed_countries' => ['IR', 'TR'],
    'messages' => [
        'not_allowed' => 'Access from your country is not allowed.',
    ],
]);

// Check access
if ($geo->isAllowed()) {
    echo "Welcome!";
} else {
    echo "Access denied";
}

// Or use guard for automatic blocking
$geo->guard(); // Automatically returns 403 error if not allowed
```

### Static Factory

```php
use MSallehi\GeoLocation\GeoLocation;

GeoLocation::create(['allowed_countries' => ['IR']])->guard();
```

## 📖 Full Documentation

### Basic Usage

```php
use MSallehi\GeoLocation\GeoLocation;

$geo = new GeoLocation();

// Get user's country
$country = $geo->getCountryFromIp();
echo "Your country: " . $country; // IR, US, GB, ...

// Get user's IP
$ip = $geo->getClientIp();

// Check if allowed
if ($geo->isAllowed()) {
    // User is allowed
}

// Get full location details
$location = $geo->getLocationDetails();
// [
//     'ip' => '5.160.139.15',
//     'country_code' => 'IR',
//     'country_name' => 'Iran',
//     'city' => 'Tehran',
//     'region' => 'Tehran',
//     'is_local' => false,
// ]
```

### Exception Handling

```php
use MSallehi\GeoLocation\GeoLocation;
use MSallehi\GeoLocation\Exceptions\CountryNotAllowedException;
use MSallehi\GeoLocation\Exceptions\GeoLocationException;

$geo = new GeoLocation(['allowed_countries' => ['IR']]);

try {
    $geo->validate();
    // User is allowed
} catch (CountryNotAllowedException $e) {
    echo $e->getMessage();
    echo "Your country: " . $e->getDetectedCountry();
    echo "Allowed countries: " . implode(', ', $e->getAllowedCountries());
    
    // Convert to JSON
    echo $e->toJson();
} catch (GeoLocationException $e) {
    echo "Location detection error: " . $e->getMessage();
}
```

### Dynamic Configuration

```php
$geo = new GeoLocation();

// Change allowed countries
$geo->setAllowedCountries(['IR', 'US', 'GB']);

// Add country
$geo->addAllowedCountry('DE');

// Remove country
$geo->removeAllowedCountry('US');

// Change error message
$geo->setMessage('not_allowed', 'This service is not available in your country.');

// Change API Provider
$geo->setApiProvider('ipinfo', ['token' => 'your-token']);

// Check specific country
if ($geo->isCountryAllowed('IR')) {
    echo "Iran is allowed";
}

// Get allowed countries list
$countries = $geo->getAllowedCountries();
```

### Check Specific IP

```php
$geo = new GeoLocation(['allowed_countries' => ['IR']]);

// Check specific IP
$isAllowed = $geo->isAllowed('5.160.139.15');
$country = $geo->getCountryFromIp('8.8.8.8');
$location = $geo->getLocationDetails('1.1.1.1');
```

---

## 🔵 Laravel Integration

### Register Service Provider

In `config/app.php`:

```php
'providers' => [
    // ...
    MSallehi\GeoLocation\Laravel\GeoLocationServiceProvider::class,
],

'aliases' => [
    // ...
    'GeoLocation' => MSallehi\GeoLocation\Laravel\GeoLocationFacade::class,
],
```

### Publish Config

```bash
php artisan vendor:publish --tag=geolocation-config
```

### Using Middleware

```php
// routes/web.php

// Only Iranian users
Route::middleware(['geolocation'])->group(function () {
    Route::get('/iran-only', function () {
        return 'Welcome!';
    });
});

// Specify countries in middleware
Route::middleware(['geolocation:IR,US,GB'])->group(function () {
    Route::get('/multi-country', function () {
        return 'Welcome!';
    });
});
```

### Using Facade

```php
use MSallehi\GeoLocation\Laravel\GeoLocationFacade as GeoLocation;

if (GeoLocation::isAllowed()) {
    // User is allowed
}

$country = GeoLocation::getCountryFromIp();
```

---

## 🟢 WordPress Integration

### Basic Setup

In your theme's `functions.php` or plugin:

```php
require_once get_template_directory() . '/vendor/autoload.php';
require_once get_template_directory() . '/vendor/msallehi/php-geolocation/src/WordPress/functions.php';
```

### Using Helper Functions

```php
// Get country
$country = geo_get_country();

// Check access
if (geo_is_allowed()) {
    echo "Welcome!";
}

// Automatic block
geo_guard(); // Calls wp_die if not allowed

// Set countries
geo_set_countries(['IR', 'TR']);

// Get full details
$location = geo_get_location();
```

### Using Shortcode

```html
[geo_restrict country="IR"]
This content is only visible to Iranian users.
[/geo_restrict]

[geo_restrict country="IR,US,GB" message="Only for selected countries"]
Special content
[/geo_restrict]
```

### Restrict Entire Page

```php
// In functions.php
add_action('template_redirect', function() {
    if (is_page('iran-only')) {
        geo_wp_restrict(['IR']);
    }
});
```

### Custom Restriction in Template

```php
// In template file
<?php
$geo = new \MSallehi\GeoLocation\GeoLocation([
    'allowed_countries' => ['IR'],
    'messages' => ['not_allowed' => 'This page is not available in your country.']
]);

if (!$geo->isAllowed()): ?>
    <div class="access-denied">
        <h2>Access Restricted</h2>
        <p>Your country: <?php echo $geo->getCountryFromIp(); ?></p>
    </div>
<?php else: ?>
    <!-- Page content -->
<?php endif; ?>
```

---

## 🌐 API Providers

### ip-api (Default - Free)

```php
$geo = new GeoLocation([
    'api_provider' => 'ip-api',
]);
```

### ipinfo.io

```php
$geo = new GeoLocation([
    'api_provider' => 'ipinfo',
    'ipinfo_token' => 'your-api-token',
]);
```

### ipdata.co

```php
$geo = new GeoLocation([
    'api_provider' => 'ipdata',
    'ipdata_api_key' => 'your-api-key',
]);
```

---

## ⚙️ Full Configuration

```php
$config = [
    // Allowed countries
    'allowed_countries' => ['IR'],
    
    // API service
    'api_provider' => 'ip-api', // ip-api, ipinfo, ipdata
    
    // API keys
    'ipinfo_token' => '',
    'ipdata_api_key' => '',
    
    // Request timeout
    'timeout' => 5,
    
    // Local IP
    'allow_local' => true,
    'local_country' => 'LOCAL',
    
    // Error messages
    'messages' => [
        'not_allowed' => 'Access from your country is not allowed.',
        'api_error' => 'Unable to determine your location.',
    ],
];

$geo = new GeoLocation($config);
```

---

## 📝 Custom Error Messages

### In Constructor

```php
$geo = new GeoLocation([
    'messages' => [
        'not_allowed' => 'Your custom access denied message',
        'api_error' => 'Custom API error message',
    ],
]);
```

### Runtime

```php
$geo->setMessage('not_allowed', 'Access denied from your country.');
```

### With denyAccess

```php
if (!$geo->isAllowed()) {
    $geo->denyAccess('Your custom message', 403);
}
```

---

## 📋 Country Codes

Use ISO 3166-1 alpha-2 codes:

| Country | Code |
|---------|------|
| Iran | IR |
| United States | US |
| United Kingdom | GB |
| Germany | DE |
| France | FR |
| Turkey | TR |
| Canada | CA |
| Australia | AU |
| UAE | AE |
| Saudi Arabia | SA |

[Full list](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2)

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 👨‍💻 Author

- **Mohammad Salehi** - [GitHub](https://github.com/MSallehi)

## ⭐ Support

If you find this package useful, please give it a star on GitHub!
