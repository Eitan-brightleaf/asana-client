<?php

namespace BrightleafDigital\Auth;

use GuzzleHttp\Exception\GuzzleException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use UnexpectedValueException;

class AsanaOAuthHandler
{
    /**
     * @property OAuth2Provider $provider The OAuth2 provider instance for Asana authentication
     */
    private OAuth2Provider $provider;

    /**
     * Initializes the OAuth2 provider with the given client configuration.
     *
     * @param string $clientId The client identifier issued by the authorization server.
     * @param string $clientSecret The client secret associated with the client ID.
     * @param string $redirectUri The URI the authorization server redirects to after authorization.
     * @return void
     */
    public function __construct(string $clientId, string $clientSecret, string $redirectUri)
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
    public function getSecureAuthorizationUrl(array $options, bool $enableState = true, bool $enablePKCE = true): array
    {
        return $this->provider->getSecureAuthorizationUrl($options, $enableState, $enablePKCE);
    }

    /**
     * Handles the callback and retrieves an access token.
     * Validates state and uses code_verifier if PKCE is enabled.
     *
     * @param string $authorizationCode The code returned by the OAuth callback
     * @param string|null $codeVerifier The PKCE code verifier (optional)
     *
     * @return AccessToken
     *
     * @throws GuzzleException
     * @throws IdentityProviderException
     * @throws UnexpectedValueException
     */
    public function handleCallback(string $authorizationCode, ?string $codeVerifier = null): AccessToken
    {
        return $this->provider->getAccessToken('authorization_code', [
            'code' => $authorizationCode,
            'code_verifier' => $codeVerifier, // Optional for PKCE
        ]);
    }

    /**
     * Retrieves the authorization URL for initiating the authentication process.
     *
     * @return string The authorization URL.
     */
    public function getAuthorizationUrl(array $options): string
    {
        return $this->provider->getAuthorizationUrl($options);
    }

    /**
     * Retrieves an access token using the provided authorization code.
     *
     * @param string $authorizationCode The authorization code received from the authorization server.
     *
     * @return AccessToken The access token details, typically including token type, expiry, and other information.
     * @throws GuzzleException
     * @throws IdentityProviderException
     */
    public function getAccessToken(string $authorizationCode): AccessToken
    {
        return $this->provider->getAccessToken('authorization_code', [
            'code' => $authorizationCode,
        ]);
    }

    /**
     * Refreshes the access token using the provided token's refresh token.
     *
     * @param AccessToken $token The current access token that contains the refresh token needed for renewal.
     *
     * @return AccessToken The newly refreshed access token.
     * @throws GuzzleException
     * @throws IdentityProviderException
     */
    public function refreshToken(AccessToken $token): AccessToken
    {
        // Get the new token from the provider
        $newToken = $this->provider->getAccessToken('refresh_token', [
            'refresh_token' => $token->getRefreshToken(),
        ]);

        // Ensure the new token retains the current refresh token
        $tokenData = $newToken->jsonSerialize();
        $tokenData['refresh_token'] = $token->getRefreshToken();

        // Return a new AccessToken instance with the updated data
        return new AccessToken($tokenData);
    }
}
