<?php

namespace BrightleafDigital\Http;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class AsanaApiClient
{
    /**
     * Response type constants
     */
    public const RESPONSE_FULL = 1;     // Return full response with status, headers, etc.
    public const RESPONSE_NORMAL = 2;   // Return the complete decoded JSON body
    public const RESPONSE_DATA = 3;     // Return only the data subset (default)

    /**
     * Default maximum number of retry attempts for rate-limited requests.
     */
    public const DEFAULT_MAX_RETRIES = 3;

    /**
     * Default initial backoff time in seconds for rate limiting.
     */
    public const DEFAULT_INITIAL_BACKOFF = 1;

    /**
     * GuzzleHttp client instance configured for Asana API communication.
     *
     * @var GuzzleClient
     */
    private GuzzleClient $httpClient;

    /**
     * PSR-3 compatible logger instance.
     *
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Maximum number of retry attempts for rate-limited requests.
     *
     * @var int
     */
    private int $maxRetries;

    /**
     * Initial backoff time in seconds for exponential backoff.
     *
     * @var int
     */
    private int $initialBackoff;

    /**
     * Creates a new Asana API client instance.
     *
     * @param string $accessToken OAuth2 access token for authentication
     * @param LoggerInterface|null $logger PSR-3 compatible logger instance
     * @param int $maxRetries Maximum number of retry attempts for rate-limited requests
     * @param int $initialBackoff Initial backoff time in seconds for exponential backoff
     */
    public function __construct(
        string $accessToken,
        ?LoggerInterface $logger = null,
        int $maxRetries = self::DEFAULT_MAX_RETRIES,
        int $initialBackoff = self::DEFAULT_INITIAL_BACKOFF
    ) {
        $this->httpClient = new GuzzleClient([
            'base_uri' => 'https://app.asana.com/api/1.0/',
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept'        => 'application/json',
            ],
        ]);
        $this->logger = $logger ?? new NullLogger();
        $this->maxRetries = $maxRetries;
        $this->initialBackoff = $initialBackoff;
    }

    /**
     * Sends an HTTP request with the specified method, URI, and options.
     *
     * This method includes automatic retry logic with exponential backoff for rate-limited
     * requests (HTTP 429). It will retry up to the configured maximum number of times,
     * respecting the Retry-After header if provided by the API.
     *
     * @param string $method The HTTP method to use (e.g., 'GET', 'POST', etc.).
     * @param string $uri The URI to make the request to.
     * @param array $options Additional options for the request, such as headers, body, and query parameters.
     * @param int $responseType The type of response to return:
     *                              - RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     * @throws AsanaApiException If the response indicates an error or if the request fails.
     * @throws RateLimitException If rate limit is exceeded and all retries are exhausted.
     */
    public function request(
        string $method,
        string $uri,
        array $options = [],
        int $responseType = self::RESPONSE_DATA
    ): array {
        return $this->executeWithRetry($method, $uri, $options, $responseType, 0);
    }

    /**
     * Execute a request with retry logic for rate limiting.
     *
     * @param string $method The HTTP method to use.
     * @param string $uri The URI to make the request to.
     * @param array $options Additional options for the request.
     * @param int $responseType The type of response to return.
     * @param int $retryCount Current retry attempt number.
     *
     * @return array The response data based on the specified response type.
     * @throws AsanaApiException If the request fails.
     * @throws RateLimitException If rate limit is exceeded and all retries are exhausted.
     */
    private function executeWithRetry(
        string $method,
        string $uri,
        array $options,
        int $responseType,
        int $retryCount
    ): array {
        $this->logger->debug('Making API request', [
            'method' => $method,
            'uri' => $uri,
            'retry_count' => $retryCount,
        ]);

        try {
            $response = $this->httpClient->request($method, $uri, $options);
            $decodedBody = json_decode((string) $response->getBody(), true);

            if (!is_array($decodedBody)) {
                $this->logger->error('Invalid JSON response from Asana API', [
                    'method' => $method,
                    'uri' => $uri,
                    'status_code' => $response->getStatusCode(),
                ]);
                throw new AsanaApiException('Invalid JSON response from Asana API.', $response->getStatusCode());
            }

            $this->logger->debug('API request successful', [
                'method' => $method,
                'uri' => $uri,
                'status_code' => $response->getStatusCode(),
            ]);

            switch ($responseType) {
                case self::RESPONSE_FULL:
                    return [
                        'status' => $response->getStatusCode(),
                        'reason' => $response->getReasonPhrase(),
                        'headers' => $response->getHeaders(),
                        'body' => $decodedBody,
                        'raw_body' => (string)$response->getBody(),
                        'request' => [
                            'method' => $method,
                            'uri' => $uri,
                            'options' => $this->sanitizeOptions($options),
                        ],
                    ];

                case self::RESPONSE_NORMAL:
                    return $decodedBody;

                case self::RESPONSE_DATA:
                default:
                    // Return just the data subset if it exists, otherwise return the full decoded body
                    return $decodedBody['data'] ?? $decodedBody;
            }
        } catch (GuzzleException $e) {
            return $this->handleGuzzleException($e, $method, $uri, $options, $responseType, $retryCount);
        }
    }

    /**
     * Handle Guzzle exceptions with rate limit retry logic.
     *
     * @param GuzzleException $e The caught exception.
     * @param string $method The HTTP method used.
     * @param string $uri The URI of the request.
     * @param array $options The request options.
     * @param int $responseType The response type requested.
     * @param int $retryCount Current retry attempt number.
     *
     * @return array The response data if retry succeeds.
     * @throws AsanaApiException If the request fails for non-rate-limit reasons.
     * @throws RateLimitException If rate limit is exceeded and all retries are exhausted.
     */
    private function handleGuzzleException(
        GuzzleException $e,
        string $method,
        string $uri,
        array $options,
        int $responseType,
        int $retryCount
    ): array {
        $response = method_exists($e, 'getResponse') ? $e->getResponse() : null;
        $statusCode = $response ? $response->getStatusCode() : 0;

        // Handle rate limiting (HTTP 429)
        if ($statusCode === 429 && $retryCount < $this->maxRetries) {
            $retryAfter = $this->getRetryAfterSeconds($response);

            $this->logger->warning('Rate limit exceeded, retrying request', [
                'method' => $method,
                'uri' => $uri,
                'retry_count' => $retryCount + 1,
                'max_retries' => $this->maxRetries,
                'retry_after_seconds' => $retryAfter,
            ]);

            // Sleep for the specified duration
            sleep($retryAfter);

            // Retry the request
            return $this->executeWithRetry($method, $uri, $options, $responseType, $retryCount + 1);
        }

        // If rate limited and out of retries, throw RateLimitException
        if ($statusCode === 429) {
            $retryAfter = $this->getRetryAfterSeconds($response);

            $this->logger->error('Rate limit exceeded, max retries exhausted', [
                'method' => $method,
                'uri' => $uri,
                'retry_count' => $retryCount,
                'max_retries' => $this->maxRetries,
            ]);

            $body = $response ? (string) $response->getBody() : '';
            $decoded = json_decode($body, true);
            $details = is_array($decoded) ? $decoded : [];

            throw new RateLimitException(
                'Rate limit exceeded. Please retry after ' . $retryAfter . ' seconds.',
                $retryAfter,
                $details,
                $e
            );
        }

        // Handle other errors
        $message = $e->getMessage();
        $details = [];

        if ($response) {
            $body = (string) $response->getBody();

            // Try to decode it as JSON (Asana usually returns structured errors)
            $decoded = json_decode($body, true);
            if (is_array($decoded) && isset($decoded['errors'][0]['message'])) {
                if (method_exists($e, 'getRequest')) {
                    $request = $e->getRequest();
                    $requestUri = $request->getUri();
                    $fullUri = $requestUri->getScheme() . '://' . $requestUri->getHost() . $requestUri->getPath() .
                        ($requestUri->getQuery() ? '?' . $requestUri->getQuery() : '');
                    $message = $request->getMethod() . ' ' . $fullUri . PHP_EOL . 'resulted in a ' .
                        $e->getCode() . ' ' . $response->getReasonPhrase() . '  : ' . PHP_EOL;
                }
                $message .= $decoded['errors'][0]['message'] . PHP_EOL . ($decoded['errors'][0]['help'] ?? '');
                $details = $decoded;
            } elseif ($body !== '') {
                // If the body isn't JSON, fall back to plain string
                $message = $body;
            }
        }

        $this->logger->error('API request failed', [
            'method' => $method,
            'uri' => $uri,
            'status_code' => $statusCode,
            'error' => $message,
        ]);

        throw new AsanaApiException($message, $e->getCode(), $details, $e);
    }

    /**
     * Get the number of seconds to wait before retrying based on the Retry-After header
     * or calculate using exponential backoff.
     *
     * @param \Psr\Http\Message\ResponseInterface|null $response The HTTP response.
     *
     * @return int The number of seconds to wait before retrying.
     */
    private function getRetryAfterSeconds($response): int
    {
        if ($response && $response->hasHeader('Retry-After')) {
            $retryAfter = $response->getHeaderLine('Retry-After');

            // Retry-After can be either a number of seconds or an HTTP-date
            if (is_numeric($retryAfter)) {
                return max(1, (int) $retryAfter);
            }

            // Try to parse as HTTP-date
            $timestamp = strtotime($retryAfter);
            if ($timestamp !== false) {
                $seconds = $timestamp - time();
                return max(1, $seconds);
            }
        }

        // Fall back to exponential backoff
        return $this->initialBackoff * (2 ** $this->maxRetries);
    }

    /**
     * Sanitize request options for logging by removing sensitive information.
     *
     * @param array $options The request options to sanitize.
     *
     * @return array The sanitized options.
     */
    private function sanitizeOptions(array $options): array
    {
        $sanitized = $options;

        // Remove Authorization header if present
        if (isset($sanitized['headers']['Authorization'])) {
            $sanitized['headers']['Authorization'] = '[REDACTED]';
        }

        return $sanitized;
    }

    /**
     * Get the configured logger instance.
     *
     * @return LoggerInterface The logger instance.
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Set a new logger instance.
     *
     * @param LoggerInterface $logger The new logger instance.
     *
     * @return self
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }
}
