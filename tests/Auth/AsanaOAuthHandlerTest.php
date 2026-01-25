<?php

namespace BrightleafDigital\Tests\Auth;

use BrightleafDigital\Auth\AsanaOAuthHandler;
use BrightleafDigital\Auth\OAuth2Provider;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AsanaOAuthHandlerTest extends TestCase
{
    private AsanaOAuthHandler $handler;
    private OAuth2Provider $mockProvider;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockProvider = $this->createMock(OAuth2Provider::class);

        // Create the handler with real credentials
        $this->handler = new AsanaOAuthHandler(
            'test-client-id',
            'test-client-secret',
            'https://example.com/callback'
        );

        // Replace the provider with our mock
        $reflection = new ReflectionClass(AsanaOAuthHandler::class);
        $providerProperty = $reflection->getProperty('provider');
        $providerProperty->setAccessible(true);
        $providerProperty->setValue($this->handler, $this->mockProvider);
    }

    /**
     * Test getAuthorizationUrl returns URL from provider.
     */
    public function testGetAuthorizationUrl(): void
    {
        $expectedUrl = 'https://app.asana.com/-/oauth_authorize?client_id=test';
        $options = ['scope' => 'tasks:read'];

        $this->mockProvider->expects($this->once())
            ->method('getAuthorizationUrl')
            ->with($options)
            ->willReturn($expectedUrl);

        $result = $this->handler->getAuthorizationUrl($options);

        $this->assertSame($expectedUrl, $result);
    }

    /**
     * Test getSecureAuthorizationUrl returns data from provider.
     */
    public function testGetSecureAuthorizationUrl(): void
    {
        $expectedResult = [
            'url' => 'https://app.asana.com/-/oauth_authorize?...',
            'state' => 'random-state-123',
            'codeVerifier' => 'random-verifier-456',
            'codeChallenge' => 'hash-789'
        ];
        $options = ['scope' => 'tasks:read'];

        $this->mockProvider->expects($this->once())
            ->method('getSecureAuthorizationUrl')
            ->with($options, true, true)
            ->willReturn($expectedResult);

        $result = $this->handler->getSecureAuthorizationUrl($options, true, true);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Test getSecureAuthorizationUrl with state disabled.
     */
    public function testGetSecureAuthorizationUrlWithoutState(): void
    {
        $expectedResult = [
            'url' => 'https://app.asana.com/-/oauth_authorize?...',
            'codeVerifier' => 'random-verifier-456',
            'codeChallenge' => 'hash-789'
        ];
        $options = ['scope' => 'tasks:read'];

        $this->mockProvider->expects($this->once())
            ->method('getSecureAuthorizationUrl')
            ->with($options, false, true)
            ->willReturn($expectedResult);

        $result = $this->handler->getSecureAuthorizationUrl($options, false, true);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Test getSecureAuthorizationUrl with PKCE disabled.
     */
    public function testGetSecureAuthorizationUrlWithoutPkce(): void
    {
        $expectedResult = [
            'url' => 'https://app.asana.com/-/oauth_authorize?...',
            'state' => 'random-state-123'
        ];
        $options = ['scope' => 'tasks:read'];

        $this->mockProvider->expects($this->once())
            ->method('getSecureAuthorizationUrl')
            ->with($options, true, false)
            ->willReturn($expectedResult);

        $result = $this->handler->getSecureAuthorizationUrl($options, true, false);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Test handleCallback exchanges code for token.
     */
    public function testHandleCallback(): void
    {
        $tokenData = [
            'access_token' => 'new-access-token',
            'refresh_token' => 'new-refresh-token',
            'expires' => time() + 3600,
        ];
        $mockToken = new AccessToken($tokenData);

        $this->mockProvider->expects($this->once())
            ->method('getAccessToken')
            ->with('authorization_code', [
                'code' => 'auth-code-123',
                'code_verifier' => 'pkce-verifier-456'
            ])
            ->willReturn($mockToken);

        $result = $this->handler->handleCallback('auth-code-123', 'pkce-verifier-456');

        $this->assertInstanceOf(AccessToken::class, $result);
        $this->assertSame('new-access-token', $result->getToken());
    }

    /**
     * Test handleCallback without PKCE code verifier.
     */
    public function testHandleCallbackWithoutCodeVerifier(): void
    {
        $tokenData = [
            'access_token' => 'new-access-token',
            'expires' => time() + 3600,
        ];
        $mockToken = new AccessToken($tokenData);

        $this->mockProvider->expects($this->once())
            ->method('getAccessToken')
            ->with('authorization_code', [
                'code' => 'auth-code-123',
                'code_verifier' => null
            ])
            ->willReturn($mockToken);

        $result = $this->handler->handleCallback('auth-code-123', null);

        $this->assertInstanceOf(AccessToken::class, $result);
    }

    /**
     * Test getAccessToken exchanges code for token.
     */
    public function testGetAccessToken(): void
    {
        $tokenData = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];
        $mockToken = new AccessToken($tokenData);

        $this->mockProvider->expects($this->once())
            ->method('getAccessToken')
            ->with('authorization_code', ['code' => 'auth-code'])
            ->willReturn($mockToken);

        $result = $this->handler->getAccessToken('auth-code');

        $this->assertInstanceOf(AccessToken::class, $result);
        $this->assertSame('test-access-token', $result->getToken());
    }

    /**
     * Test refreshToken refreshes an existing token.
     */
    public function testRefreshToken(): void
    {
        $oldTokenData = [
            'access_token' => 'old-access-token',
            'refresh_token' => 'refresh-token-123',
            'expires' => time() - 3600, // Expired
        ];
        $oldToken = new AccessToken($oldTokenData);

        $newTokenData = [
            'access_token' => 'new-access-token',
            'expires' => time() + 3600,
        ];
        $newToken = new AccessToken($newTokenData);

        $this->mockProvider->expects($this->once())
            ->method('getAccessToken')
            ->with('refresh_token', ['refresh_token' => 'refresh-token-123'])
            ->willReturn($newToken);

        $result = $this->handler->refreshToken($oldToken);

        $this->assertInstanceOf(AccessToken::class, $result);
        $this->assertSame('new-access-token', $result->getToken());
        // The refresh token should be preserved
        $this->assertSame('refresh-token-123', $result->getRefreshToken());
    }

    /**
     * Test refreshToken preserves original refresh token in new token.
     */
    public function testRefreshTokenPreservesRefreshToken(): void
    {
        $originalRefreshToken = 'original-refresh-token';
        $oldToken = new AccessToken([
            'access_token' => 'old-access-token',
            'refresh_token' => $originalRefreshToken,
            'expires' => time() - 3600,
        ]);

        // New token from provider doesn't include refresh token
        $newTokenFromProvider = new AccessToken([
            'access_token' => 'new-access-token',
            'expires' => time() + 3600,
        ]);

        $this->mockProvider->expects($this->once())
            ->method('getAccessToken')
            ->with('refresh_token', ['refresh_token' => $originalRefreshToken])
            ->willReturn($newTokenFromProvider);

        $result = $this->handler->refreshToken($oldToken);

        // The original refresh token should be preserved
        $this->assertSame($originalRefreshToken, $result->getRefreshToken());
    }

    /**
     * Test constructor creates provider with correct parameters.
     */
    public function testConstructorCreatesProvider(): void
    {
        $handler = new AsanaOAuthHandler(
            'my-client-id',
            'my-client-secret',
            'https://my-app.com/callback'
        );

        $reflection = new ReflectionClass(AsanaOAuthHandler::class);
        $providerProperty = $reflection->getProperty('provider');
        $providerProperty->setAccessible(true);
        $provider = $providerProperty->getValue($handler);

        $this->assertInstanceOf(OAuth2Provider::class, $provider);
    }

    /**
     * Test multiple authorization URL calls generate consistent structure.
     */
    public function testMultipleAuthorizationUrlCalls(): void
    {
        $options = ['scope' => 'tasks:read'];

        $this->mockProvider->expects($this->exactly(2))
            ->method('getAuthorizationUrl')
            ->with($options)
            ->willReturn('https://example.com/auth');

        $url1 = $this->handler->getAuthorizationUrl($options);
        $url2 = $this->handler->getAuthorizationUrl($options);

        $this->assertSame($url1, $url2);
    }
}
