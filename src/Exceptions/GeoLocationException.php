<?php

namespace MSallehi\GeoLocation\Exceptions;

use Exception;

class GeoLocationException extends Exception
{
    protected ?string $countryCode;
    protected array $context;

    public function __construct(
        string $message = "GeoLocation error occurred",
        int $code = 0,
        ?Exception $previous = null,
        ?string $countryCode = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->countryCode = $countryCode;
        $this->context = $context;
    }

    /**
     * Get the country code associated with the exception
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * Get additional context for the exception
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
