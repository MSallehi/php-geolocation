# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

<p align="center">
  <a href="./CHANGELOG.fa.md">🇮🇷 تغییرات به فارسی</a>
</p>

---

## [1.1.0] - 2024-12-02

### Added
- **Fallback Allow**: New `fallback_allow` config option - when API fails, users are allowed by default (prevents blocking legitimate users due to API issues)
- **Multiple API Fallback**: If primary API provider fails, automatically tries fallback providers
- **Retry Mechanism**: Configurable retry count for failed API requests
- **Connect Timeout**: Separate `connect_timeout` setting for faster failure detection
- **In-Memory Cache**: Built-in caching to reduce API calls and improve performance
- **WordPress Debug Function**: New `geo_debug_info()` function to help troubleshoot issues
- **Safe Check Function**: New `geo_is_allowed_safe()` function that never throws exceptions
- **Better Error Messages**: More descriptive error messages showing detected country and allowed countries

### Fixed
- **Critical Bug**: Users from allowed countries (like Iran) were incorrectly blocked when API timeout occurred
- **WordPress Integration**: Fixed issue where API failures caused `wp_die()` without proper explanation
- **Exception Handling**: Improved error handling to prevent false blocks

### Changed
- Default behavior now allows access when API fails (configurable via `fallback_allow`)
- WordPress functions now include `fallback_allow: true` by default
- Improved `geo_wp_restrict()` to show more detailed error messages

### Security
- No security vulnerabilities fixed in this release

---

## [1.0.0] - 2024-12-01

### Added
- Initial release
- Country detection from IP address
- Support for multiple API providers (ip-api, ipinfo, ipdata)
- Laravel integration (ServiceProvider, Middleware, Facade)
- WordPress integration (helper functions, shortcodes)
- Pure PHP support
- Customizable error messages
- ISO 3166-1 alpha-2 country code support

---

## Upgrade Guide

### From 1.0.0 to 1.1.0

No breaking changes. Simply update via composer:

```bash
composer update msallehi/php-geolocation
```

**Recommended**: Review the new `fallback_allow` setting. It defaults to `true` (allow access on API failure), which is the recommended setting for production to avoid blocking legitimate users.

If you prefer strict mode (block on API failure), set:

```php
$geo = new GeoLocation([
    'fallback_allow' => false,
]);
```
