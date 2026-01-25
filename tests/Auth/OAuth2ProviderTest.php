<?php

namespace BrightleafDigital\Tests\Auth;

use BrightleafDigital\Auth\OAuth2Provider;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class OAuth2ProviderTest extends TestCase
{
    private OAuth2Provider $provider;

    protected function setUp(): void
    {
        $this->provider = new OAuth2Provider([
            'clientId' => 'test-client-id',
            'clientSecret' => 'test-client-secret',
            'redirectUri' => 'https://example.com/callback',
        ]);
    }

    /**
     * Test getBaseAuthorizationUrl returns correct Asana URL.
     */
    public function testGetBaseAuthorizationUrl(): void
    {
        $url = $this->provider->getBaseAuthorizationUrl();

        $this->assertSame('https://app.asana.com/-/oauth_authorize', $url);
    }

    /**
     * Test getBaseAccessTokenUrl returns correct Asana URL.
     */
    public function testGetBaseAccessTokenUrl(): void
    {
        $url = $this->provider->getBaseAccessTokenUrl([]);

        $this->assertSame('https://app.asana.com/-/oauth_token', $url);
    }

    /**
     * Test getResourceOwnerDetailsUrl returns correct Asana URL.
     */
    public function testGetResourceOwnerDetailsUrl(): void
    {
        $token = new AccessToken(['access_token' => 'test-token']);
        $url = $this->provider->getResourceOwnerDetailsUrl($token);

        $this->assertSame('https://app.asana.com/api/1.0/users/me', $url);
    }

    /**
     * Test getAuthorizationUrl generates valid URL.
     */
    public function testGetAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl(['scope' => 'default']);

        $this->assertStringStartsWith('https://app.asana.com/-/oauth_authorize', $url);
        $this->assertStringContainsString('client_id=test-client-id', $url);
        $this->assertStringContainsString('redirect_uri=', $url);
        $this->assertStringContainsString('scope=default', $url);
        $this->assertStringContainsString('response_type=code', $url);
    }

    /**
     * Test getSecureAuthorizationUrl with state and PKCE enabled.
     */
    public function testGetSecureAuthorizationUrlWithStateAndPkce(): void
    {
        $result = $this->provider->getSecureAuthorizationUrl(
            ['scope' => 'tasks:read projects:read'],
            true,
            true
        );

        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('state', $result);
        $this->assertArrayHasKey('codeVerifier', $result);
        $this->assertArrayHasKey('codeChallenge', $result);

        // URL should contain the state and code challenge
        $this->assertStringContainsString('state=', $result['url']);
        $this->assertStringContainsString('code_challenge=', $result['url']);
        $this->assertStringContainsString('code_challenge_method=S256', $result['url']);

        // State should be 32 hex characters (16 bytes)
        $this->assertSame(32, strlen($result['state']));

        // Code verifier should be 64 hex characters (32 bytes)
        $this->assertSame(64, strlen($result['codeVerifier']));

        // Code challenge should be base64url encoded
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $result['codeChallenge']);
    }

    /**
     * Test getSecureAuthorizationUrl with state only.
     */
    public function testGetSecureAuthorizationUrlWithStateOnly(): void
    {
        $result = $this->provider->getSecureAuthorizationUrl(
            ['scope' => 'tasks:read'],
            true,
            false
        );

        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('state', $result);
        $this->assertArrayNotHasKey('codeVerifier', $result);
        $this->assertArrayNotHasKey('codeChallenge', $result);

        $this->assertStringContainsString('state=', $result['url']);
        $this->assertStringNotContainsString('code_challenge=', $result['url']);
    }

    /**
     * Test getSecureAuthorizationUrl with PKCE only.
     */
    public function testGetSecureAuthorizationUrlWithPkceOnly(): void
    {
        $result = $this->provider->getSecureAuthorizationUrl(
            ['scope' => 'tasks:read'],
            false,
            true
        );

        $this->assertArrayHasKey('url', $result);
        $this->assertArrayNotHasKey('state', $result);
        $this->assertArrayHasKey('codeVerifier', $result);
        $this->assertArrayHasKey('codeChallenge', $result);

        $this->assertStringContainsString('code_challenge=', $result['url']);
        $this->assertStringContainsString('code_challenge_method=S256', $result['url']);
    }

    /**
     * Test getSecureAuthorizationUrl without custom state or PKCE.
     * Note: The parent OAuth2 provider always adds a state for security.
     */
    public function testGetSecureAuthorizationUrlWithoutCustomStateOrPkce(): void
    {
        $result = $this->provider->getSecureAuthorizationUrl(
            ['scope' => 'tasks:read'],
            false,
            false
        );

        $this->assertArrayHasKey('url', $result);
        // When useState is false, no state is returned in the result array
        $this->assertArrayNotHasKey('state', $result);
        $this->assertArrayNotHasKey('codeVerifier', $result);

        // The parent OAuth2 class may still add state to URL for security
        $this->assertStringNotContainsString('code_challenge=', $result['url']);
    }

    /**
     * Test PKCE code challenge is SHA256 hash of code verifier.
     */
    public function testPkceCodeChallengeIsCorrectHash(): void
    {
        $result = $this->provider->getSecureAuthorizationUrl(
            ['scope' => 'tasks:read'],
            false,
            true
        );

        // Manually calculate what the code challenge should be
        $expectedChallenge = rtrim(strtr(
            base64_encode(hash('sha256', $result['codeVerifier'], true)),
            '+/',
            '-_'
        ), '=');

        $this->assertSame($expectedChallenge, $result['codeChallenge']);
    }

    /**
     * Test state is cryptographically random.
     */
    public function testStateIsRandom(): void
    {
        $states = [];
        for ($i = 0; $i < 10; $i++) {
            $result = $this->provider->getSecureAuthorizationUrl(
                ['scope' => 'tasks:read'],
                true,
                false
            );
            $states[] = $result['state'];
        }

        // All states should be unique
        $this->assertCount(10, array_unique($states));
    }

    /**
     * Test code verifier is cryptographically random.
     */
    public function testCodeVerifierIsRandom(): void
    {
        $verifiers = [];
        for ($i = 0; $i < 10; $i++) {
            $result = $this->provider->getSecureAuthorizationUrl(
                ['scope' => 'tasks:read'],
                false,
                true
            );
            $verifiers[] = $result['codeVerifier'];
        }

        // All verifiers should be unique
        $this->assertCount(10, array_unique($verifiers));
    }

    /**
     * Test authorization URL contains all required OAuth parameters.
     */
    public function testAuthorizationUrlContainsRequiredParams(): void
    {
        $result = $this->provider->getSecureAuthorizationUrl(
            ['scope' => 'openid email profile'],
            true,
            true
        );

        $parsedUrl = parse_url($result['url']);
        parse_str($parsedUrl['query'], $queryParams);

        $this->assertArrayHasKey('client_id', $queryParams);
        $this->assertArrayHasKey('redirect_uri', $queryParams);
        $this->assertArrayHasKey('response_type', $queryParams);
        $this->assertArrayHasKey('scope', $queryParams);
        $this->assertArrayHasKey('state', $queryParams);
        $this->assertArrayHasKey('code_challenge', $queryParams);
        $this->assertArrayHasKey('code_challenge_method', $queryParams);

        $this->assertSame('test-client-id', $queryParams['client_id']);
        $this->assertSame('code', $queryParams['response_type']);
        $this->assertSame('S256', $queryParams['code_challenge_method']);
    }

    /**
     * Test scope is properly URL encoded.
     */
    public function testScopeIsProperlyEncoded(): void
    {
        $result = $this->provider->getSecureAuthorizationUrl(
            ['scope' => 'tasks:read projects:write'],
            false,
            false
        );

        // The scope should be in the URL (spaces become %20 or +)
        $this->assertTrue(
            strpos($result['url'], 'scope=tasks%3Aread') !== false ||
            strpos($result['url'], 'scope=tasks:read') !== false
        );
    }

    /**
     * Test provider can be created with minimal configuration.
     */
    public function testProviderWithMinimalConfig(): void
    {
        $provider = new OAuth2Provider([
            'clientId' => 'client-id',
            'clientSecret' => 'client-secret',
            'redirectUri' => 'https://example.com/redirect',
        ]);

        $this->assertSame(
            'https://app.asana.com/-/oauth_authorize',
            $provider->getBaseAuthorizationUrl()
        );
    }
}
