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

    public function getBaseAccessTokenUrl(array $params): string
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