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

    /**
     * Returns authorization data like URL, state, and PKCE verifier (if enabled)
     *
     * @param bool $enableState
     * @param bool $enablePKCE
     * @return array ['url' => string, 'state' => string|null, 'codeVerifier' => string|null]
     */
    public function getSecureAuthorizationUrl(bool $enableState = true, bool $enablePKCE = true): array
    {
        return $this->provider->getSecureAuthorizationUrl($enableState, $enablePKCE);
    }

    /**
     * Handles the callback and retrieves an access token.
     * Validates state and uses code_verifier if PKCE is enabled.
     *
     * @param string $authorizationCode The code returned by the OAuth callback
     * @param string|null $codeVerifier The PKCE code verifier (optional)
     * @return AccessToken
     *
     * @throws \RuntimeException If state validation fails
     */
    public function handleCallback(string $authorizationCode, ?string $codeVerifier = null): AccessToken
    {
        return $this->provider->getAccessToken('authorization_code', [
            'code' => $authorizationCode,
            'code_verifier' => $codeVerifier, // Optional for PKCE
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