<?php

namespace MSallehi\GeoLocation;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use MSallehi\GeoLocation\Exceptions\GeoLocationException;
use MSallehi\GeoLocation\Exceptions\CountryNotAllowedException;

class GeoLocation
{
    protected Client $client;
    protected array $config;
    protected static array $cache = [];

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->client = new Client([
            'timeout' => $this->config['timeout'],
            'connect_timeout' => $this->config['connect_timeout'] ?? 3,
        ]);
    }

    /**
     * Get default configuration
     */
    protected function getDefaultConfig(): array
    {
        return [
            'allowed_countries' => ['IR'],
            'api_provider' => 'ip-api',
            'timeout' => 5,
            'connect_timeout' => 3,
            'messages' => [
                'not_allowed' => 'Access from your country is not allowed.',
                'api_error' => 'Unable to determine your location.',
            ],
            'cache_enabled' => true,
            'cache_ttl' => 3600,
            'fallback_allow' => true, // Allow access if API fails
            'retry_count' => 2,
            'fallback_providers' => ['ip-api', 'ipinfo'], // Fallback order
        ];
    }

    /**
     * Get country code from IP address
     * 
     * Priority order:
     * 1. CDN/Proxy country headers (Cloudflare, CloudFront, etc.) - Fast & Free
     * 2. In-memory cache
     * 3. GeoIP API call
     */
    public function getCountryFromIp(?string $ip = null): ?string
    {
        // PRIORITY 1: Check CDN/Proxy country headers (fast, free, reliable)
        // This works when site is behind Cloudflare, CloudFront, or has GeoIP module
        if ($ip === null) {
            $cdnCountry = $this->getCountryFromCdnHeaders();
            if ($cdnCountry !== null) {
                return $cdnCountry;
            }
        }

        // PRIORITY 2: Get IP and check local
        $ip = $ip ?? $this->getClientIp();

        if ($this->isLocalIp($ip)) {
            return $this->config['local_country'] ?? 'LOCAL';
        }

        // PRIORITY 3: Check cache
        if ($this->config['cache_enabled'] && isset(self::$cache[$ip])) {
            $cached = self::$cache[$ip];
            if ($cached['expires'] > time()) {
                return $cached['country_code'];
            }
            unset(self::$cache[$ip]);
        }

        // PRIORITY 4: Call GeoIP API
        try {
            $countryCode = $this->fetchCountryFromApiWithFallback($ip);
            
            // Cache the result
            if ($this->config['cache_enabled']) {
                self::$cache[$ip] = [
                    'country_code' => $countryCode,
                    'expires' => time() + $this->config['cache_ttl'],
                ];
            }
            
            return $countryCode;
        } catch (\Exception $e) {
            // If fallback_allow is true, return null (will be treated as allowed)
            if ($this->config['fallback_allow']) {
                return null;
            }
            throw new GeoLocationException(
                $this->config['messages']['api_error'],
                0,
                $e
            );
        }
    }

    /**
     * Get country code from CDN/Proxy headers
     * 
     * Many CDN providers and reverse proxies add country headers automatically.
     * This is faster and more reliable than API calls.
     * 
     * @return string|null Country code (ISO 3166-1 alpha-2) or null if not available
     */
    protected function getCountryFromCdnHeaders(): ?string
    {
        $cdnCountryHeaders = [
            'HTTP_CF_IPCOUNTRY',               // Cloudflare
            'HTTP_CLOUDFRONT_VIEWER_COUNTRY',  // AWS CloudFront
            'HTTP_X_VERCEL_IP_COUNTRY',        // Vercel
            'HTTP_X_COUNTRY_CODE',             // Generic proxy header
            'HTTP_GEOIP_COUNTRY_CODE',         // NGINX/Apache GeoIP module
            'HTTP_X_GEO_COUNTRY',              // Some CDNs
        ];
        
        foreach ($cdnCountryHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                $country = strtoupper(trim($_SERVER[$header]));
                
                // Validate: must be exactly 2 uppercase letters
                // XX means Cloudflare couldn't determine the country
                // T1 means Tor exit node in Cloudflare
                if ($country !== 'XX' && $country !== 'T1' && preg_match('/^[A-Z]{2}$/', $country)) {
                    return $country;
                }
            }
        }
        
        return null;
    }

    /**
     * Check if IP is from allowed countries
     */
    public function isAllowed(?string $ip = null): bool
    {
        try {
            $country = $this->getCountryFromIp($ip);
            
            // If country is null (API failed and fallback_allow is true), allow access
            if ($country === null) {
                return true;
            }

            if ($country === 'LOCAL') {
                return $this->config['allow_local'] ?? true;
            }

            return in_array($country, $this->config['allowed_countries'], true);
        } catch (\Exception $e) {
            // On any error, check fallback_allow setting
            return $this->config['fallback_allow'] ?? true;
        }
    }

    /**
     * Validate IP and throw exception if not allowed
     */
    public function validate(?string $ip = null): void
    {
        if (!$this->isAllowed($ip)) {
            $country = $this->getCountryFromIp($ip);
            throw new CountryNotAllowedException(
                $this->config['messages']['not_allowed'],
                $country,
                $this->config['allowed_countries']
            );
        }
    }

    /**
     * Get location details from IP
     */
    public function getLocationDetails(?string $ip = null): array
    {
        $ip = $ip ?? $this->getClientIp();

        if ($this->isLocalIp($ip)) {
            return [
                'ip' => $ip,
                'country_code' => 'LOCAL',
                'country_name' => 'Local Network',
                'city' => null,
                'region' => null,
                'is_local' => true,
            ];
        }

        try {
            return $this->fetchLocationFromApi($ip);
        } catch (\Exception $e) {
            return [
                'ip' => $ip,
                'country_code' => null,
                'country_name' => null,
                'city' => null,
                'region' => null,
                'is_local' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch country code from API with fallback providers
     */
    protected function fetchCountryFromApiWithFallback(string $ip): string
    {
        $providers = $this->config['fallback_providers'] ?? ['ip-api', 'ipinfo'];
        $lastException = null;
        
        // Make sure primary provider is first
        $primaryProvider = $this->config['api_provider'];
        if (!in_array($primaryProvider, $providers)) {
            array_unshift($providers, $primaryProvider);
        } else {
            // Move primary to front
            $providers = array_diff($providers, [$primaryProvider]);
            array_unshift($providers, $primaryProvider);
        }
        
        foreach ($providers as $provider) {
            for ($retry = 0; $retry < ($this->config['retry_count'] ?? 2); $retry++) {
                try {
                    $response = $this->callApiByProvider($provider, $ip);
                    return $response['country_code'] ?? 'UNKNOWN';
                } catch (\Exception $e) {
                    $lastException = $e;
                    // Small delay before retry
                    if ($retry < ($this->config['retry_count'] ?? 2) - 1) {
                        usleep(100000); // 100ms
                    }
                }
            }
        }
        
        throw $lastException ?? new GeoLocationException("All API providers failed for IP: {$ip}");
    }

    /**
     * Fetch country code from API (legacy method)
     */
    protected function fetchCountryFromApi(string $ip): string
    {
        return $this->fetchCountryFromApiWithFallback($ip);
    }

    /**
     * Fetch full location from API
     */
    protected function fetchLocationFromApi(string $ip): array
    {
        return $this->callApi($ip);
    }

    /**
     * Call API by provider name
     */
    protected function callApiByProvider(string $provider, string $ip): array
    {
        return match ($provider) {
            'ip-api' => $this->callIpApi($ip),
            'ip-api-ir' => $this->callIpApiIr($ip),
            'ipinfo' => $this->callIpInfo($ip),
            'ipdata' => $this->callIpData($ip),
            default => $this->callIpApi($ip),
        };
    }

    /**
     * Call the geolocation API
     */
    protected function callApi(string $ip): array
    {
        $provider = $this->config['api_provider'];

        return match ($provider) {
            'ip-api' => $this->callIpApi($ip),
            'ip-api-ir' => $this->callIpApiIr($ip),
            'ipinfo' => $this->callIpInfo($ip),
            'ipdata' => $this->callIpData($ip),
            default => $this->callIpApi($ip),
        };
    }

    /**
     * Call ip-api.com (free, no API key required)
     */
    protected function callIpApi(string $ip): array
    {
        $response = $this->client->get("http://ip-api.com/json/{$ip}");
        $data = json_decode($response->getBody()->getContents(), true);

        if ($data['status'] === 'fail') {
            throw new GeoLocationException("Failed to get location for IP: {$ip}");
        }

        return [
            'ip' => $ip,
            'country_code' => $data['countryCode'] ?? null,
            'country_name' => $data['country'] ?? null,
            'city' => $data['city'] ?? null,
            'region' => $data['regionName'] ?? null,
            'is_local' => false,
        ];
    }

    /**
     * Call ipinfo.io (requires API key for production)
     */
    protected function callIpInfo(string $ip): array
    {
        $token = $this->config['ipinfo_token'] ?? '';
        $url = "https://ipinfo.io/{$ip}/json";

        if ($token) {
            $url .= "?token={$token}";
        }

        $response = $this->client->get($url);
        $data = json_decode($response->getBody()->getContents(), true);

        return [
            'ip' => $ip,
            'country_code' => $data['country'] ?? null,
            'country_name' => null,
            'city' => $data['city'] ?? null,
            'region' => $data['region'] ?? null,
            'is_local' => false,
        ];
    }

    /**
     * Call ipdata.co (requires API key)
     */
    protected function callIpData(string $ip): array
    {
        $apiKey = $this->config['ipdata_api_key'] ?? '';
        $url = "https://api.ipdata.co/{$ip}?api-key={$apiKey}";

        $response = $this->client->get($url);
        $data = json_decode($response->getBody()->getContents(), true);

        return [
            'ip' => $ip,
            'country_code' => $data['country_code'] ?? null,
            'country_name' => $data['country_name'] ?? null,
            'city' => $data['city'] ?? null,
            'region' => $data['region'] ?? null,
            'is_local' => false,
        ];
    }

    /**
     * Call ip-api.ir (Iranian GeoIP service, optimized for Iranian servers)
     * 
     * This is a Persian/Iranian geolocation API that works well from Iranian servers
     * where international APIs might be slow or blocked.
     * 
     * @see https://ip-api.ir/
     */
    protected function callIpApiIr(string $ip): array
    {
        $guid = $this->config['ip_api_ir_guid'] ?? '';
        
        // Build URL based on whether GUID is provided
        if ($guid) {
            $url = "https://ip-api.ir/info/{$guid}/{$ip}";
        } else {
            $url = "https://ip-api.ir/info/{$ip}/country,countryCode,regionName,city";
        }

        $response = $this->client->get($url);
        $data = json_decode($response->getBody()->getContents(), true);

        return [
            'ip' => $ip,
            'country_code' => $data['countryCode'] ?? null,
            'country_name' => $data['country'] ?? null,
            'city' => $data['city'] ?? null,
            'region' => $data['regionName'] ?? null,
            'is_local' => false,
        ];
    }

    /**
     * Get client IP address
     */
    public function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_FORWARDED_FOR',      // Proxy
            'HTTP_X_REAL_IP',            // Nginx
            'HTTP_CLIENT_IP',            // Client IP
            'REMOTE_ADDR',               // Standard
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);

                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '127.0.0.1';
    }

    /**
     * Check if IP is local/private
     */
    protected function isLocalIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }

    /**
     * Set allowed countries
     */
    public function setAllowedCountries(array $countries): self
    {
        $this->config['allowed_countries'] = array_map('strtoupper', $countries);
        return $this;
    }

    /**
     * Add country to allowed list
     */
    public function addAllowedCountry(string $country): self
    {
        $this->config['allowed_countries'][] = strtoupper($country);
        return $this;
    }

    /**
     * Set custom error message
     */
    public function setMessage(string $key, string $message): self
    {
        $this->config['messages'][$key] = $message;
        return $this;
    }

    /**
     * Get current configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get all allowed countries
     */
    public function getAllowedCountries(): array
    {
        return $this->config['allowed_countries'];
    }

    /**
     * Remove a country from allowed list
     */
    public function removeAllowedCountry(string $country): self
    {
        $country = strtoupper($country);
        $this->config['allowed_countries'] = array_filter(
            $this->config['allowed_countries'],
            fn($c) => $c !== $country
        );
        return $this;
    }

    /**
     * Check if a specific country is allowed
     */
    public function isCountryAllowed(string $country): bool
    {
        return in_array(strtoupper($country), $this->config['allowed_countries'], true);
    }

    /**
     * Set API provider
     */
    public function setApiProvider(string $provider, array $credentials = []): self
    {
        $this->config['api_provider'] = $provider;

        if (isset($credentials['token'])) {
            $this->config['ipinfo_token'] = $credentials['token'];
        }
        if (isset($credentials['api_key'])) {
            $this->config['ipdata_api_key'] = $credentials['api_key'];
        }

        return $this;
    }

    /**
     * Deny access with custom response (for pure PHP)
     */
    public function denyAccess(?string $message = null, int $statusCode = 403): void
    {
        $message = $message ?? $this->config['messages']['not_allowed'];

        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => false,
            'error' => true,
            'message' => $message,
            'country' => $this->getCountryFromIp(),
            'allowed_countries' => $this->config['allowed_countries'],
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    /**
     * Check and deny if not allowed (shorthand for pure PHP)
     */
    public function guard(?string $ip = null): void
    {
        if (!$this->isAllowed($ip)) {
            $this->denyAccess();
        }
    }

    /**
     * Create a new instance with config (static factory)
     */
    public static function create(array $config = []): self
    {
        return new self($config);
    }
}
