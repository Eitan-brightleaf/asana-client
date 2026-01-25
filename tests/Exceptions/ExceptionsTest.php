<?php

namespace BrightleafDigital\Tests\Exceptions;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Exceptions\OAuthCallbackException;
use BrightleafDigital\Exceptions\TokenInvalidException;
use Exception;
use PHPUnit\Framework\TestCase;

class ExceptionsTest extends TestCase
{
    // ========================================
    // AsanaApiException Tests
    // ========================================

    /**
     * Test AsanaApiException can be created with message only.
     */
    public function testAsanaApiExceptionWithMessageOnly(): void
    {
        $exception = new AsanaApiException('Test error message');

        $this->assertSame('Test error message', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame([], $exception->getResponseData());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * Test AsanaApiException can be created with message and code.
     */
    public function testAsanaApiExceptionWithMessageAndCode(): void
    {
        $exception = new AsanaApiException('Not found', 404);

        $this->assertSame('Not found', $exception->getMessage());
        $this->assertSame(404, $exception->getCode());
        $this->assertSame([], $exception->getResponseData());
    }

    /**
     * Test AsanaApiException can be created with response data.
     */
    public function testAsanaApiExceptionWithResponseData(): void
    {
        $responseData = [
            'errors' => [
                ['message' => 'task: Not a valid gid', 'help' => 'Check the ID format']
            ]
        ];

        $exception = new AsanaApiException('Invalid task', 400, $responseData);

        $this->assertSame('Invalid task', $exception->getMessage());
        $this->assertSame(400, $exception->getCode());
        $this->assertSame($responseData, $exception->getResponseData());
    }

    /**
     * Test AsanaApiException can be created with previous exception.
     */
    public function testAsanaApiExceptionWithPreviousException(): void
    {
        $previous = new Exception('Original error');
        $exception = new AsanaApiException('Wrapped error', 500, [], $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * Test AsanaApiException getResponseData returns empty array by default.
     */
    public function testAsanaApiExceptionGetResponseDataReturnsEmptyArray(): void
    {
        $exception = new AsanaApiException('Error');

        $this->assertIsArray($exception->getResponseData());
        $this->assertEmpty($exception->getResponseData());
    }

    /**
     * Test AsanaApiException extends Exception class.
     */
    public function testAsanaApiExceptionExtendsException(): void
    {
        $exception = new AsanaApiException('Error');

        $this->assertInstanceOf(Exception::class, $exception);
    }

    // ========================================
    // OAuthCallbackException Tests
    // ========================================

    /**
     * Test OAuthCallbackException can be created with message only.
     */
    public function testOAuthCallbackExceptionWithMessageOnly(): void
    {
        $exception = new OAuthCallbackException('OAuth error');

        $this->assertSame('OAuth error', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame([], $exception->getData());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * Test OAuthCallbackException can be created with message and code.
     */
    public function testOAuthCallbackExceptionWithMessageAndCode(): void
    {
        $exception = new OAuthCallbackException('Unauthorized', 401);

        $this->assertSame('Unauthorized', $exception->getMessage());
        $this->assertSame(401, $exception->getCode());
    }

    /**
     * Test OAuthCallbackException can be created with data.
     */
    public function testOAuthCallbackExceptionWithData(): void
    {
        $data = [
            'authorization_code' => 'abc**xyz',
            'code_verifier' => 'Provided',
            'context' => 'OAuth callback'
        ];

        $exception = new OAuthCallbackException('Callback failed', 400, $data);

        $this->assertSame($data, $exception->getData());
    }

    /**
     * Test OAuthCallbackException can be created with previous exception.
     */
    public function testOAuthCallbackExceptionWithPreviousException(): void
    {
        $previous = new Exception('Network error');
        $exception = new OAuthCallbackException('Callback failed', 0, [], $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * Test OAuthCallbackException getData returns empty array by default.
     */
    public function testOAuthCallbackExceptionGetDataReturnsEmptyArray(): void
    {
        $exception = new OAuthCallbackException('Error');

        $this->assertIsArray($exception->getData());
        $this->assertEmpty($exception->getData());
    }

    /**
     * Test OAuthCallbackException extends Exception class.
     */
    public function testOAuthCallbackExceptionExtendsException(): void
    {
        $exception = new OAuthCallbackException('Error');

        $this->assertInstanceOf(Exception::class, $exception);
    }

    /**
     * Test OAuthCallbackException with complex data structure.
     */
    public function testOAuthCallbackExceptionWithComplexData(): void
    {
        $data = [
            'context' => 'OAuth callback',
            'response_data' => [
                'http_status' => 400,
                'http_reason' => 'Bad Request',
                'response_body' => '{"error": "invalid_grant"}',
                'response_headers' => ['Content-Type' => ['application/json']]
            ]
        ];

        $exception = new OAuthCallbackException('Token exchange failed', 400, $data);

        $this->assertSame($data, $exception->getData());
        $this->assertSame(400, $exception->getData()['response_data']['http_status']);
    }

    // ========================================
    // TokenInvalidException Tests
    // ========================================

    /**
     * Test TokenInvalidException can be created with message only.
     */
    public function testTokenInvalidExceptionWithMessageOnly(): void
    {
        $exception = new TokenInvalidException('Token expired');

        $this->assertSame('Token expired', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame([], $exception->getData());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * Test TokenInvalidException can be created with message and code.
     */
    public function testTokenInvalidExceptionWithMessageAndCode(): void
    {
        $exception = new TokenInvalidException('Invalid token', 401);

        $this->assertSame('Invalid token', $exception->getMessage());
        $this->assertSame(401, $exception->getCode());
    }

    /**
     * Test TokenInvalidException can be created with data.
     */
    public function testTokenInvalidExceptionWithData(): void
    {
        $data = [
            'context' => 'Refresh token',
            'error' => 'Token has been revoked'
        ];

        $exception = new TokenInvalidException('Refresh failed', 401, $data);

        $this->assertSame($data, $exception->getData());
    }

    /**
     * Test TokenInvalidException can be created with previous exception.
     */
    public function testTokenInvalidExceptionWithPreviousException(): void
    {
        $previous = new Exception('API error');
        $exception = new TokenInvalidException('Token invalid', 0, [], $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * Test TokenInvalidException getData returns empty array by default.
     */
    public function testTokenInvalidExceptionGetDataReturnsEmptyArray(): void
    {
        $exception = new TokenInvalidException('Error');

        $this->assertIsArray($exception->getData());
        $this->assertEmpty($exception->getData());
    }

    /**
     * Test TokenInvalidException extends Exception class.
     */
    public function testTokenInvalidExceptionExtendsException(): void
    {
        $exception = new TokenInvalidException('Error');

        $this->assertInstanceOf(Exception::class, $exception);
    }

    /**
     * Test TokenInvalidException common use case - no access token.
     */
    public function testTokenInvalidExceptionNoAccessToken(): void
    {
        $exception = new TokenInvalidException('No access token is available.');

        $this->assertSame('No access token is available.', $exception->getMessage());
    }

    /**
     * Test TokenInvalidException common use case - OAuth handler not configured.
     */
    public function testTokenInvalidExceptionOAuthNotConfigured(): void
    {
        $exception = new TokenInvalidException('OAuth handler is not configured.');

        $this->assertSame('OAuth handler is not configured.', $exception->getMessage());
    }

    // ========================================
    // Cross-Exception Tests
    // ========================================

    /**
     * Test all three exception types can be caught as Exception.
     */
    public function testAllExceptionsCanBeCaughtAsException(): void
    {
        $exceptions = [
            new AsanaApiException('API error'),
            new OAuthCallbackException('OAuth error'),
            new TokenInvalidException('Token error'),
        ];

        foreach ($exceptions as $exception) {
            $caught = false;
            try {
                throw $exception;
            } catch (Exception $e) {
                $caught = true;
            }
            $this->assertTrue($caught, get_class($exception) . ' should be catchable as Exception');
        }
    }

    /**
     * Test exception message interpolation for error during context.
     */
    public function testTokenInvalidExceptionWithContext(): void
    {
        $context = 'Refresh token';
        $originalMessage = 'Network timeout';
        $message = "Error during {$context}: {$originalMessage}";

        $exception = new TokenInvalidException($message, 0, ['context' => $context]);

        $this->assertStringContainsString($context, $exception->getMessage());
        $this->assertStringContainsString($originalMessage, $exception->getMessage());
    }

    /**
     * Test full exception chain.
     */
    public function testExceptionChaining(): void
    {
        $networkError = new Exception('Connection refused');
        $oauthError = new OAuthCallbackException('Failed to exchange code', 0, [], $networkError);
        $tokenError = new TokenInvalidException('Cannot authenticate', 0, [], $oauthError);

        $this->assertSame($oauthError, $tokenError->getPrevious());
        $this->assertSame($networkError, $tokenError->getPrevious()->getPrevious());
    }
}
