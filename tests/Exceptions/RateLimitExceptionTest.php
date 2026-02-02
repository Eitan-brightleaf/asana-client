<?php

namespace BrightleafDigital\Tests\Exceptions;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RateLimitExceptionTest extends TestCase
{
    /**
     * Test that RateLimitException extends AsanaApiException.
     */
    public function testExtendsAsanaApiException(): void
    {
        $exception = new RateLimitException('Rate limit exceeded');
        $this->assertInstanceOf(AsanaApiException::class, $exception);
    }

    /**
     * Test constructor with default values.
     */
    public function testConstructorWithDefaults(): void
    {
        $exception = new RateLimitException('Rate limit exceeded');

        $this->assertSame('Rate limit exceeded', $exception->getMessage());
        $this->assertSame(429, $exception->getCode());
        $this->assertSame(60, $exception->getRetryAfter());
        $this->assertSame([], $exception->getResponseData());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * Test constructor with custom retry after value.
     */
    public function testConstructorWithCustomRetryAfter(): void
    {
        $exception = new RateLimitException('Rate limit exceeded', 120);

        $this->assertSame(120, $exception->getRetryAfter());
        $this->assertSame(429, $exception->getCode());
    }

    /**
     * Test constructor with response data.
     */
    public function testConstructorWithResponseData(): void
    {
        $responseData = [
            'errors' => [
                ['message' => 'Rate limit exceeded', 'help' => 'Wait and try again']
            ]
        ];

        $exception = new RateLimitException('Rate limit exceeded', 30, $responseData);

        $this->assertSame($responseData, $exception->getResponseData());
    }

    /**
     * Test constructor with previous exception.
     */
    public function testConstructorWithPreviousException(): void
    {
        $previous = new RuntimeException('Original error');
        $exception = new RateLimitException('Rate limit exceeded', 60, [], $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * Test getRetryAfter returns correct value.
     */
    public function testGetRetryAfter(): void
    {
        $exception = new RateLimitException('Test', 45);
        $this->assertSame(45, $exception->getRetryAfter());
    }

    /**
     * Test that HTTP status code is always 429.
     */
    public function testHttpStatusCodeIs429(): void
    {
        $exception = new RateLimitException('Test', 30);
        $this->assertSame(429, $exception->getCode());
    }
}
