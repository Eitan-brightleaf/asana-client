<?php

namespace BrightleafDigital\Exceptions;

use Exception;
use Throwable;

class TokenInvalidException extends Exception
{
    protected array $data;

    /**
     * Class constructor.
     *
     * @param string $message The exception message.
     * @param int $code The exception code (default is 0).
     * @param array $data Additional data related to the exception.
     * @param Throwable|null $previous The previous throwable used for exception chaining.
     *
     * @return void
     */
    public function __construct(string $message, int $code = 0, array $data = [], Throwable $previous = null)
    {
        $this->data = $data;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Retrieves the data stored in the current object.
     *
     * @return array The array of data.
     */
    public function getData(): array
    {
        return $this->data;
    }
}
