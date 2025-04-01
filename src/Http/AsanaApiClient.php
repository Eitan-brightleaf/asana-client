<?php

namespace BrightleafDigital\Http;

use GuzzleHttp\Client as GuzzleClient;

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
     * Sends an HTTP request using the specified method, URI, and options, and returns the decoded JSON response.
     *
     * @param string $method The HTTP method to use for the request (e.g., GET, POST, PUT, DELETE).
     * @param string $uri The URI of the endpoint to send the request to.
     * @param array $options Optional configuration options for the request (e.g., headers, query parameters, body).
     *
     * @return mixed The decoded JSON response body.
     */
    public function request(string $method, string $uri, array $options = [])
    {
        $response = $this->httpClient->request($method, $uri, $options);
        return json_decode($response->getBody(), true);
    }
}