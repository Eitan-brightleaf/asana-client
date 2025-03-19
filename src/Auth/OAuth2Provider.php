<?php


namespace BrightleafDigital\Auth;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class OAuth2Provider extends AbstractProvider
{
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://app.asana.com/-/oauth_authorize';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://app.asana.com/-/oauth_token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://app.asana.com/api/1.0/users/me';
    }

    protected function getDefaultScopes(): array
    {
        return ['default'];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error'])) {
            throw new \RuntimeException($data['error']);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return $response;
    }
}