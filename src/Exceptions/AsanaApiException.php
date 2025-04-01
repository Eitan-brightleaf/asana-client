<?php

namespace BrightleafDigital\Exceptions;

use Exception;
use Throwable;

/**
 * Custom exception class for handling Asana API errors.
 */
class AsanaApiException extends Exception
{
    protected array $responseData;

    /**
     * Constructor for AsanaApiException.
     *
     * @param string $message The exception message.
     * @param int $code The HTTP status code or internal error code.
     * @param array $responseData Optional decoded response body or error data.
     * @param Throwable|null $previous The previous throwable used for exception chaining.
     */
    public function __construct(string $message, int $code = 0, array $responseData = [], Throwable $previous = null)
    {
        $this->responseData = $responseData;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the response data returned from the Asana API.
     *
     * @return array
     */
    public function getResponseData(): array
    {
        return $this->responseData;
    }
}
