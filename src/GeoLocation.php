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

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->client = new Client([
            'timeout' => $this->config['timeout'],
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
            'messages' => [
                'not_allowed' => 'Access from your country is not allowed.',
                'api_error' => 'Unable to determine your location.',
            ],
            'cache_enabled' => true,
            'cache_ttl' => 3600,
        ];
    }

    /**
     * Get country code from IP address
     */
    public function getCountryFromIp(?string $ip = null): ?string
    {
        $ip = $ip ?? $this->getClientIp();

        if ($this->isLocalIp($ip)) {
            return $this->config['local_country'] ?? 'LOCAL';
        }

        try {
            return $this->fetchCountryFromApi($ip);
        } catch (GuzzleException $e) {
            throw new GeoLocationException(
                $this->config['messages']['api_error'],
                0,
                $e
            );
        }
    }

    /**
     * Check if IP is from allowed countries
     */
    public function isAllowed(?string $ip = null): bool
    {
        $country = $this->getCountryFromIp($ip);

        if ($country === 'LOCAL') {
            return $this->config['allow_local'] ?? true;
        }

        return in_array($country, $this->config['allowed_countries'], true);
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

        return $this->fetchLocationFromApi($ip);
    }

    /**
     * Fetch country code from API
     */
    protected function fetchCountryFromApi(string $ip): string
    {
        $response = $this->callApi($ip);
        return $response['country_code'] ?? 'UNKNOWN';
    }

    /**
     * Fetch full location from API
     */
    protected function fetchLocationFromApi(string $ip): array
    {
        return $this->callApi($ip);
    }

    /**
     * Call the geolocation API
     */
    protected function callApi(string $ip): array
    {
        $provider = $this->config['api_provider'];

        return match ($provider) {
            'ip-api' => $this->callIpApi($ip),
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
