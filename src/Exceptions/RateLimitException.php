<?php

namespace BrightleafDigital\Exceptions;

/**
 * Exception thrown when the Asana API rate limit is exceeded.
 *
 * This exception provides additional context about rate limiting,
 * including the retry-after duration suggested by the API.
 */
class RateLimitException extends AsanaApiException
{
    /**
     * The number of seconds to wait before retrying the request.
     */
    private int $retryAfter;

    /**
     * Constructor for RateLimitException.
     *
     * @param string $message The exception message.
     * @param int $retryAfter The number of seconds to wait before retrying.
     * @param array $responseData Optional decoded response body or error data.
     * @param \Throwable|null $previous The previous throwable used for exception chaining.
     */
    public function __construct(
        string $message,
        int $retryAfter = 60,
        array $responseData = [],
        ?\Throwable $previous = null
    ) {
        $this->retryAfter = $retryAfter;
        parent::__construct($message, 429, $responseData, $previous);
    }

    /**
     * Get the number of seconds to wait before retrying.
     *
     * @return int The retry-after duration in seconds.
     */
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
