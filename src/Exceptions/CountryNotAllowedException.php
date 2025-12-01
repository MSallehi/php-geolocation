<?php

namespace MSallehi\GeoLocation\Exceptions;

class CountryNotAllowedException extends GeoLocationException
{
    protected array $allowedCountries;
    protected ?string $detectedCountry;

    public function __construct(
        string $message = "Access from your country is not allowed",
        ?string $detectedCountry = null,
        array $allowedCountries = [],
        int $code = 403,
        ?\Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous, $detectedCountry, [
            'detected_country' => $detectedCountry,
            'allowed_countries' => $allowedCountries,
        ]);

        $this->detectedCountry = $detectedCountry;
        $this->allowedCountries = $allowedCountries;
    }

    /**
     * Get the detected country code
     */
    public function getDetectedCountry(): ?string
    {
        return $this->detectedCountry;
    }

    /**
     * Get list of allowed countries
     */
    public function getAllowedCountries(): array
    {
        return $this->allowedCountries;
    }

    /**
     * Convert exception to array
     */
    public function toArray(): array
    {
        return [
            'error' => true,
            'message' => $this->getMessage(),
            'detected_country' => $this->detectedCountry,
            'allowed_countries' => $this->allowedCountries,
            'code' => $this->getCode(),
        ];
    }

    /**
     * Convert exception to JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }
}
