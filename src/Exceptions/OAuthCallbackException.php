<?php

namespace BrightleafDigital\Exceptions;

use Exception;
use Throwable;

class OAuthCallbackException extends Exception
{
    protected array $data;

    /**
     * Constructor for the class.
     *
     * @param string $message The exception message.
     * @param int $code The exception code (default is 0).
     * @param array $data An array containing additional response data.
     * @param Throwable|null $previous The previous throwable used for exception chaining.
     *
     */
    public function __construct(string $message, int $code = 0, array $data = [], Throwable $previous = null)
    {
        $this->data = $data;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Retrieves the response data.
     *
     * @return array The response data.
     */
    public function getData(): array
    {
        return $this->data;
    }
}
