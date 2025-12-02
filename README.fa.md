<div dir="rtl">

# PHP GeoLocation - پکیج محدودسازی دسترسی بر اساس کشور

[![Latest Version on Packagist](https://img.shields.io/packagist/v/msallehi/php-geolocation.svg?style=flat-square)](https://packagist.org/packages/msallehi/php-geolocation)
[![Total Downloads](https://img.shields.io/packagist/dt/msallehi/php-geolocation.svg?style=flat-square)](https://packagist.org/packages/msallehi/php-geolocation)
[![License](https://img.shields.io/packagist/l/msallehi/php-geolocation.svg?style=flat-square)](https://packagist.org/packages/msallehi/php-geolocation)
[![PHP Version](https://img.shields.io/packagist/php-v/msallehi/php-geolocation.svg?style=flat-square)](https://packagist.org/packages/msallehi/php-geolocation)

یک پکیج PHP برای محدود کردن دسترسی کاربران بر اساس کشور / IP. **سازگار با PHP خالص، لاراول، وردپرس و هر فریمورک PHP دیگری.**

<p align="center">
  <a href="./README.md">🇬🇧 English Documentation</a> |
  <a href="./CHANGELOG.fa.md">📋 تغییرات</a>
</p>

## ✨ ویژگی‌ها

- 🌍 تشخیص خودکار کشور از روی IP
- 🔒 محدود کردن دسترسی به کشورهای خاص
- ⚙️ تنظیمات کاملاً قابل تغییر
- 🔧 پشتیبانی از چندین API Provider
- 📝 پیام‌های خطای سفارشی
- 🎯 سازگار با PHP خالص، لاراول و وردپرس
- 🔄 Fallback خودکار در صورت خطای API (نسخه 1.1.0+)

## 📦 نصب

</div>

```bash
composer require msallehi/php-geolocation
```

<div dir="rtl">

## 🚀 شروع سریع

### PHP خالص

</div>

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

<div dir="rtl">

### فکتوری استاتیک

</div>

```php
use MSallehi\GeoLocation\GeoLocation;

GeoLocation::create(['allowed_countries' => ['IR']])->guard();
```

<div dir="rtl">

## 📖 مستندات کامل

### استفاده پایه

</div>

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

<div dir="rtl">

### مدیریت خطا

</div>

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

<div dir="rtl">

### تنظیم پویا

</div>

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

<div dir="rtl">

### چک کردن IP خاص

</div>

```php
$geo = new GeoLocation(['allowed_countries' => ['IR']]);

// چک کردن IP خاص
$isAllowed = $geo->isAllowed('5.160.139.15');
$country = $geo->getCountryFromIp('8.8.8.8');
$location = $geo->getLocationDetails('1.1.1.1');
```

---

<div dir="rtl">

## 🔵 یکپارچگی با لاراول

### ثبت Service Provider

در `config/app.php`:

</div>

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

<div dir="rtl">

### انتشار فایل تنظیمات

</div>

```bash
php artisan vendor:publish --tag=geolocation-config
```

<div dir="rtl">

### استفاده از Middleware

</div>

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

<div dir="rtl">

### استفاده از Facade

</div>

```php
use MSallehi\GeoLocation\Laravel\GeoLocationFacade as GeoLocation;

if (GeoLocation::isAllowed()) {
    // کاربر مجاز است
}

$country = GeoLocation::getCountryFromIp();
```

---

<div dir="rtl">

## 🟢 یکپارچگی با وردپرس

### راه‌اندازی اولیه

در `functions.php` تم یا پلاگین خود:

</div>

```php
require_once get_template_directory() . '/vendor/autoload.php';
require_once get_template_directory() . '/vendor/msallehi/php-geolocation/src/WordPress/functions.php';
```

<div dir="rtl">

### استفاده از توابع کمکی

</div>

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

<div dir="rtl">

### استفاده از Shortcode

</div>

```html
[geo_restrict country="IR"]
این محتوا فقط برای کاربران ایرانی قابل مشاهده است.
[/geo_restrict]

[geo_restrict country="IR,US,GB" message="فقط برای کشورهای منتخب"]
محتوای خاص
[/geo_restrict]
```

<div dir="rtl">

### محدود کردن کل صفحه

</div>

```php
// در functions.php
add_action('template_redirect', function() {
    if (is_page('iran-only')) {
        geo_wp_restrict(['IR']);
    }
});
```

<div dir="rtl">

### محدودسازی سفارشی در قالب

</div>

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

<div dir="rtl">

## 🌐 سرویس‌دهندگان API

### ip-api (پیش‌فرض - رایگان)

</div>

```php
$geo = new GeoLocation([
    'api_provider' => 'ip-api',
]);
```

<div dir="rtl">

### ipinfo.io

</div>

```php
$geo = new GeoLocation([
    'api_provider' => 'ipinfo',
    'ipinfo_token' => 'your-api-token',
]);
```

<div dir="rtl">

### ipdata.co

</div>

```php
$geo = new GeoLocation([
    'api_provider' => 'ipdata',
    'ipdata_api_key' => 'your-api-key',
]);
```

---

<div dir="rtl">

## ⚙️ تنظیمات کامل

</div>

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

<div dir="rtl">

## 📝 پیام‌های خطای سفارشی

### در Constructor

</div>

```php
$geo = new GeoLocation([
    'messages' => [
        'not_allowed' => 'پیام سفارشی شما برای عدم دسترسی',
        'api_error' => 'پیام سفارشی خطای API',
    ],
]);
```

<div dir="rtl">

### در زمان اجرا

</div>

```php
$geo->setMessage('not_allowed', 'دسترسی از کشور شما امکان‌پذیر نیست.');
```

<div dir="rtl">

### با denyAccess

</div>

```php
if (!$geo->isAllowed()) {
    $geo->denyAccess('پیام سفارشی شما', 403);
}
```

---

<div dir="rtl">

## 📋 کدهای کشور

استفاده از کدهای ISO 3166-1 alpha-2:

| کشور | کد |
|------|-----|
| ایران | IR |
| آمریکا | US |
| انگلیس | GB |
| آلمان | DE |
| فرانسه | FR |
| ترکیه | TR |
| کانادا | CA |
| استرالیا | AU |
| امارات | AE |
| عربستان | SA |

[لیست کامل](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2)

---

## 📄 مجوز

The MIT License (MIT). برای اطلاعات بیشتر [فایل مجوز](LICENSE) را مشاهده کنید.

## 🤝 مشارکت

مشارکت‌ها استقبال می‌شوند! لطفاً Pull Request ارسال کنید.

## 👨‍💻 نویسنده

- **محمد صالحی** - [GitHub](https://github.com/MSallehi)

## ⭐ حمایت

اگر این پکیج برایتان مفید بود، لطفاً در GitHub ستاره بدهید!

</div>
