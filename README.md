# PHP GeoLocation - IP/Country Restriction Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/msallehi/php-geolocation.svg?style=flat-square)](https://packagist.org/packages/msallehi/php-geolocation)
[![Total Downloads](https://img.shields.io/packagist/dt/msallehi/php-geolocation.svg?style=flat-square)](https://packagist.org/packages/msallehi/php-geolocation)

یک پکیج PHP برای محدود کردن دسترسی کاربران بر اساس کشور / IP

A PHP package to restrict user access based on their country/IP location. **Works with pure PHP, Laravel, WordPress, and any PHP framework.**

## ✨ Features | ویژگی‌ها

- 🌍 تشخیص خودکار کشور از روی IP
- 🔒 محدود کردن دسترسی به کشورهای خاص
- ⚙️ تنظیمات کاملاً قابل تغییر
- 🔧 پشتیبانی از چندین API Provider
- 📝 پیام‌های خطای سفارشی
- 🎯 سازگار با PHP خالص، لاراول و وردپرس

## 📦 Installation | نصب

```bash
composer require msallehi/php-geolocation
```

## 🚀 Quick Start | شروع سریع

### Pure PHP | پی‌اچ‌پی خالص

```php
<?php
require 'vendor/autoload.php';

use MSallehi\GeoLocation\GeoLocation;

// ایجاد نمونه با تنظیمات پیش‌فرض (فقط ایران)
$geo = new GeoLocation();

// یا با تنظیمات سفارشی
$geo = new GeoLocation([
    'allowed_countries' => ['IR', 'TR'],
    'messages' => [
        'not_allowed' => 'دسترسی از کشور شما امکان‌پذیر نیست.',
    ],
]);

// چک کردن دسترسی
if ($geo->isAllowed()) {
    echo "خوش آمدید!";
} else {
    echo "دسترسی ندارید";
}

// یا استفاده از guard برای بلاک خودکار
$geo->guard(); // اگر مجاز نباشد، خودکار خطای 403 می‌دهد
```

### Static Factory | فکتوری استاتیک

```php
use MSallehi\GeoLocation\GeoLocation;

GeoLocation::create(['allowed_countries' => ['IR']])->guard();
```

## 📖 Full Documentation | مستندات کامل

### Basic Usage | استفاده پایه

```php
use MSallehi\GeoLocation\GeoLocation;

$geo = new GeoLocation();

// دریافت کشور کاربر
$country = $geo->getCountryFromIp();
echo "کشور شما: " . $country; // IR, US, GB, ...

// دریافت IP کاربر
$ip = $geo->getClientIp();

// چک کردن مجاز بودن
if ($geo->isAllowed()) {
    // کاربر مجاز است
}

// دریافت اطلاعات کامل موقعیت
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

### Exception Handling | مدیریت خطا

```php
use MSallehi\GeoLocation\GeoLocation;
use MSallehi\GeoLocation\Exceptions\CountryNotAllowedException;
use MSallehi\GeoLocation\Exceptions\GeoLocationException;

$geo = new GeoLocation(['allowed_countries' => ['IR']]);

try {
    $geo->validate();
    // کاربر مجاز است
} catch (CountryNotAllowedException $e) {
    echo $e->getMessage();
    echo "کشور شما: " . $e->getDetectedCountry();
    echo "کشورهای مجاز: " . implode(', ', $e->getAllowedCountries());
    
    // تبدیل به JSON
    echo $e->toJson();
} catch (GeoLocationException $e) {
    echo "خطا در تشخیص موقعیت: " . $e->getMessage();
}
```

### Dynamic Configuration | تنظیم پویا

```php
$geo = new GeoLocation();

// تغییر کشورهای مجاز
$geo->setAllowedCountries(['IR', 'US', 'GB']);

// اضافه کردن کشور
$geo->addAllowedCountry('DE');

// حذف کشور
$geo->removeAllowedCountry('US');

// تغییر پیام خطا
$geo->setMessage('not_allowed', 'متأسفانه این سرویس در کشور شما در دسترس نیست.');

// تغییر API Provider
$geo->setApiProvider('ipinfo', ['token' => 'your-token']);

// چک کردن یک کشور خاص
if ($geo->isCountryAllowed('IR')) {
    echo "ایران مجاز است";
}

// دریافت لیست کشورهای مجاز
$countries = $geo->getAllowedCountries();
```

### Check Specific IP | چک کردن IP خاص

```php
$geo = new GeoLocation(['allowed_countries' => ['IR']]);

// چک کردن IP خاص
$isAllowed = $geo->isAllowed('5.160.139.15');
$country = $geo->getCountryFromIp('8.8.8.8');
$location = $geo->getLocationDetails('1.1.1.1');
```

---

## 🔵 Laravel Integration | یکپارچگی با لاراول

### Register Service Provider

در `config/app.php`:

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

// فقط کاربران ایرانی
Route::middleware(['geolocation'])->group(function () {
    Route::get('/iran-only', function () {
        return 'خوش آمدید!';
    });
});

// تعیین کشورها در middleware
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
    // کاربر مجاز است
}

$country = GeoLocation::getCountryFromIp();
```

---

## 🟢 WordPress Integration | یکپارچگی با وردپرس

### Basic Setup

در `functions.php` تم یا پلاگین خود:

```php
require_once get_template_directory() . '/vendor/autoload.php';
require_once get_template_directory() . '/vendor/msallehi/php-geolocation/src/WordPress/functions.php';
```

### Using Helper Functions

```php
// دریافت کشور
$country = geo_get_country();

// چک کردن دسترسی
if (geo_is_allowed()) {
    echo "خوش آمدید!";
}

// بلاک خودکار
geo_guard(); // اگر مجاز نباشد، wp_die می‌کند

// تنظیم کشورها
geo_set_countries(['IR', 'TR']);

// دریافت اطلاعات کامل
$location = geo_get_location();
```

### Using Shortcode

```html
[geo_restrict country="IR"]
این محتوا فقط برای کاربران ایرانی قابل مشاهده است.
[/geo_restrict]

[geo_restrict country="IR,US,GB" message="فقط برای کشورهای منتخب"]
محتوای خاص
[/geo_restrict]
```

### Restrict Entire Page

```php
// در functions.php
add_action('template_redirect', function() {
    if (is_page('iran-only')) {
        geo_wp_restrict(['IR']);
    }
});
```

### Custom Restriction in Template

```php
// در template file
<?php
$geo = new \MSallehi\GeoLocation\GeoLocation([
    'allowed_countries' => ['IR'],
    'messages' => ['not_allowed' => 'این صفحه برای کشور شما در دسترس نیست.']
]);

if (!$geo->isAllowed()): ?>
    <div class="access-denied">
        <h2>دسترسی محدود</h2>
        <p>کشور شما: <?php echo $geo->getCountryFromIp(); ?></p>
    </div>
<?php else: ?>
    <!-- محتوای صفحه -->
<?php endif; ?>
```

---

## 🌐 API Providers | سرویس‌دهندگان API

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

## ⚙️ Full Configuration | تنظیمات کامل

```php
$config = [
    // کشورهای مجاز
    'allowed_countries' => ['IR'],
    
    // سرویس API
    'api_provider' => 'ip-api', // ip-api, ipinfo, ipdata
    
    // کلیدهای API
    'ipinfo_token' => '',
    'ipdata_api_key' => '',
    
    // timeout درخواست
    'timeout' => 5,
    
    // IP محلی
    'allow_local' => true,
    'local_country' => 'LOCAL',
    
    // پیام‌های خطا
    'messages' => [
        'not_allowed' => 'دسترسی از کشور شما امکان‌پذیر نیست.',
        'api_error' => 'امکان تشخیص موقعیت شما وجود ندارد.',
    ],
];

$geo = new GeoLocation($config);
```

---

## 📝 Custom Error Messages | پیام‌های خطای سفارشی

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
    $geo->denyAccess('پیام سفارشی شما', 403);
}
```

---

## 📋 Country Codes | کدهای کشور

استفاده از کدهای ISO 3166-1 alpha-2:

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

[لیست کامل](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2)

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
