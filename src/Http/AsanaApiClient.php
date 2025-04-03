<?php

namespace BrightleafDigital\Http;

use BrightleafDigital\Exceptions\AsanaApiException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

class AsanaApiClient
{
    /**
     * GuzzleHttp client instance configured for Asana API communication.
     *
     * @var GuzzleClient
     */
    private GuzzleClient $httpClient;

    /**
     * Creates a new Asana API client instance.
     *
     * @param string $accessToken OAuth2 access token for authentication
     */
    public function __construct(string $accessToken)
    {
        $this->httpClient = new GuzzleClient([
            'base_uri' => 'https://app.asana.com/api/1.0/',
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept'        => 'application/json',
            ],
        ]);
    }

    /**
     * Sends an HTTP request with the specified method, URI, and options.
     *
     * @param string $method The HTTP method to use (e.g., 'GET', 'POST', etc.).
     * @param string $uri The URI to make the request to.
     * @param array $options Additional options for the request, such as headers, body, and query parameters.
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     * @return array The response data. If $fullResponse is true, it includes status, reason, headers, body,
     *               raw body, and the request details; otherwise, it returns the decoded body directly.
     * @throws AsanaApiException If the response indicates an error or if the request fails.
     */
    public function request(string $method, string $uri, array $options = [], bool $fullResponse = false): array
    {
        try {
            $response = $this->httpClient->request($method, $uri, $options);
            if ($fullResponse) {
                return [
                    'status' => $response->getStatusCode(),
                    'reason' => $response->getReasonPhrase(),
                    'headers' => $response->getHeaders(),
                    'body' => json_decode($response->getBody(), true),
                    'raw_body' => (string)$response->getBody(),
                    'request' => [
                        'method' => $method,
                        'uri' => $uri,
                        'options' => $options,
                    ],
                ];
            }
            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            $message = '';
            $details = [];

            if (method_exists($e, 'hasResponse') && $e->hasResponse() && method_exists($e, 'getResponse')) {
                $response = $e->getResponse();
                $body = (string) $response->getBody();

                // Try to decode it as JSON (Asana usually returns structured errors)
                $decoded = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    if (isset($decoded['errors'][0]['message'])) {
                        if (method_exists($e, 'getRequest')) {
                            $request = $e->getRequest();
                            $uri = $request->getUri();
                            $uri = $uri->getScheme() . '://' . $uri->getHost() . $uri->getPath() .
                                ($uri->getQuery() ? '?' . $uri->getQuery() : '');
                            $message = $request->getMethod() . ' ' . $uri . PHP_EOL . 'resulted in a ' .
                                $e->getCode() . ' ' . $response->getReasonPhrase() . '  : ' . PHP_EOL;
                        }
                        $message .= $decoded['errors'][0]['message'] . PHP_EOL . $decoded['errors'][0]['help'];
                    }
                    $details = $decoded;
                } else {
                    // If the body isn’t JSON, fall back to plain string
                    $message = $body;
                }
            } else {
                // Fall back to the short Guzzle error if no response
                $message = $e->getMessage();
            }

            throw new AsanaApiException($message, $e->getCode(), $details, $e);
        }
    }
}
