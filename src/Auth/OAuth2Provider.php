<?php

namespace BrightleafDigital\Auth;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class OAuth2Provider extends AbstractProvider
{
    /**
     * Retrieves the base authorization URL for the OAuth process.
     *
     * @return string The base authorization URL.
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://app.asana.com/-/oauth_authorize';
    }

    /**
     * Returns the base URL for retrieving the access token.
     *
     * @param array $params An array of parameters for constructing the URL.
     * @return string The base access token URL.
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://app.asana.com/-/oauth_token';
    }

    /**
     * Returns the URL to fetch resource owner details.
     *
     * @param AccessToken $token The access token used for authentication.
     * @return string The URL to retrieve resource owner details.
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://app.asana.com/api/1.0/users/me';
    }

    /**
     * Retrieves a list of default scopes for authorization.
     *
     * @return array List of default scopes.
     */
    protected function getDefaultScopes(): array
    {
        return ['default'];
    }

    /**
     * Checks the response for errors and throws an exception if an error is found.
     *
     * @param ResponseInterface $response The HTTP response object.
     * @param mixed $data The response data to be checked for errors.
     * @return void
     * @throws RuntimeException if an error is found in the response data.
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error'])) {
            throw new RuntimeException($data['error']);
        }
    }

    /**
     * Creates and returns the resource owner using the given response and access token.
     *
     * @param array $response The response data retrieved from the resource server.
     * @param AccessToken $token The access token associated with the resource owner.
     * @return array The processed resource owner data.
     */
    protected function createResourceOwner(array $response, AccessToken $token): array
    {
        return $response;
    }

    /**
     * Extends getAuthorizationUrl to optionally include state and PKCE
     *
     * @param bool $enableState
     * @param bool $enablePKCE
     * @return array ['url' => string, 'state' => string|null, 'codeVerifier' => string|null]
     */
    public function getSecureAuthorizationUrl(bool $enableState = true, bool $enablePKCE = true): array
    {
        $extras = [];
        $options = [];

        // Generate a state parameter if enabled
        if ($enableState) {
            $extras['state'] = bin2hex(random_bytes(16)); // Generate secure random state
            $options['state'] = $extras['state'];
        }

        // Generate PKCE verifier and code challenge if enabled
        if ($enablePKCE) {
            $extras['codeVerifier'] = bin2hex(random_bytes(32)); // A 32-byte code verifier
            $extras['codeChallenge'] = rtrim(strtr(
                base64_encode(hash('sha256', $extras['codeVerifier'], true)),
                '+/',
                '-_'
            ), '=');
            $options['code_challenge'] = $extras['codeChallenge'];
            $options['code_challenge_method'] = 'S256'; // Use SHA-256
        }

        // Generate the authorization URL
        $extras['url'] = parent::getAuthorizationUrl($options);

        return $extras; // The user will get the URL and any extras (state, PKCE verifier, etc.)
    }
}
