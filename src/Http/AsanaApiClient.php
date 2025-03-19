<?php

namespace BrightleafDigital\Http;

use GuzzleHttp\Client as GuzzleClient;

class AsanaApiClient
{
    private GuzzleClient $httpClient;

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

    public function request(string $method, string $uri, array $options = [])
    {
        $response = $this->httpClient->request($method, $uri, $options);
        return json_decode($response->getBody(), true);
    }
}