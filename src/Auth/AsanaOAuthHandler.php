<?php

namespace BrightleafDigital\Auth;

use League\OAuth2\Client\Token\AccessToken;

class AsanaOAuthHandler
{
    private OAuth2Provider $provider;

    public function __construct($clientId, $clientSecret, $redirectUri)
    {
        $this->provider = new OAuth2Provider([
            'clientId'     => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri'  => $redirectUri,
        ]);
    }

    public function getAuthorizationUrl(): string
    {
        return $this->provider->getAuthorizationUrl();
    }

    public function getAccessToken($authorizationCode)
    {
        return $this->provider->getAccessToken('authorization_code', [
            'code' => $authorizationCode,
        ]);
    }

    public function refreshToken(AccessToken $token)
    {
        return $this->provider->getAccessToken('refresh_token', [
            'refresh_token' => $token->getRefreshToken(),
        ]);
    }
}