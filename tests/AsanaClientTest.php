<?php

/** @noinspection PhpParamsInspection */

namespace BrightleafDigital\Tests;

use BrightleafDigital\Api\AttachmentApiService;
use BrightleafDigital\Api\CustomFieldApiService;
use BrightleafDigital\Api\MembershipApiService;
use BrightleafDigital\Api\ProjectApiService;
use BrightleafDigital\Api\SectionApiService;
use BrightleafDigital\Api\TagsApiService;
use BrightleafDigital\Api\TaskApiService;
use BrightleafDigital\Api\UserApiService;
use BrightleafDigital\Api\WorkspaceApiService;
use BrightleafDigital\AsanaClient;
use BrightleafDigital\Auth\AsanaOAuthHandler;
use BrightleafDigital\Exceptions\OAuthCallbackException;
use BrightleafDigital\Exceptions\TokenInvalidException;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Utils\CryptoUtils;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use ReflectionClass;
use ReflectionException;

class AsanaClientTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/asana_client_tests_' . uniqid('', true);
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0700, true);
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->tempDir) && is_dir($this->tempDir)) {
            $this->deleteDir($this->tempDir);
        }
    }

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
    /**
     * Test that loadToken returns true when a valid token file exists and can be loaded.
     */
    public function testLoadTokenReturnsTrueWhenTokenExists(): void
    {
        $password = 'test_password';
        $tokenStoragePath = $this->tempDir . '/valid_token.json';
        $plainToken = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];
        $encrypted = [
            'access_token' => CryptoUtils::encrypt($plainToken['access_token'], $password),
            'refresh_token' => CryptoUtils::encrypt($plainToken['refresh_token'], $password),
            'expires' => $plainToken['expires'],
        ];
        file_put_contents($tokenStoragePath, json_encode($encrypted));

        $client = new AsanaClient(null, null, null, $tokenStoragePath);

        $this->assertTrue($client->loadToken($password));
        $this->assertEquals($plainToken, $client->getAccessToken());
    }

    /**
     * Test that loadToken returns false when the token file does not exist.
     */
    public function testLoadTokenReturnsFalseWhenTokenDoesNotExist(): void
    {
        $password = 'test_password';
        $tokenStoragePath = $this->tempDir . '/non_existent_token.json';

        $client = new AsanaClient(null, null, null, $tokenStoragePath);

        $this->assertFalse($client->loadToken($password));
    }

    /**
     * Test that loadToken handles invalid token file gracefully.
     */
    public function testLoadTokenHandlesInvalidFileGracefully(): void
    {
        $password = 'test_password';
        $tokenStoragePath = $this->tempDir . '/invalid_token.json';
        file_put_contents($tokenStoragePath, 'Not a valid JSON');

        $client = new AsanaClient(null, null, null, $tokenStoragePath);

        $this->assertFalse($client->loadToken($password));
    }
    /**
     * Test that refreshToken() successfully refreshes an expired token.
     * @throws MockException|TokenInvalidException
     */
    public function testRefreshTokenSuccessfullyRefreshesToken(): void
    {
        $expiredToken = $this->createMock(AccessToken::class);
        $expiredToken->method('hasExpired')->willReturn(true);

        $newAccessToken = new AccessToken(['access_token' => 'new-access-token']);
        $mockAuthHandler = $this->createMock(AsanaOAuthHandler::class);
        $mockAuthHandler->expects($this->once())
            ->method('refreshToken')
            ->with($expiredToken)
            ->willReturn($newAccessToken);

        $client = $this->getMockBuilder(AsanaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(AsanaClient::class);
        $authHandlerProperty = $reflection->getProperty('authHandler');
        $authHandlerProperty->setAccessible(true);
        $authHandlerProperty->setValue($client, $mockAuthHandler);

        $accessTokenProperty = $reflection->getProperty('accessToken');
        $accessTokenProperty->setAccessible(true);
        $accessTokenProperty->setValue($client, $expiredToken);

        $result = $client->refreshToken();

        $this->assertEquals($newAccessToken->jsonSerialize(), $result);
    }

    /**
     * Test that refreshToken() throws TokenInvalidException if no token exists.
     */
    public function testRefreshTokenThrowsExceptionIfNoToken(): void
    {
        $client = new AsanaClient();

        $this->expectException(TokenInvalidException::class);
        $this->expectExceptionMessage('No access token is available.');

        $client->refreshToken();
    }

    /**
     * Test that refreshToken() throws TokenInvalidException on a GuzzleException.
     * @throws MockException
     */
    public function testRefreshTokenThrowsExceptionOnGuzzleException(): void
    {
        $expiredToken = $this->createMock(AccessToken::class);
        $expiredToken->method('hasExpired')->willReturn(true);

        $mockAuthHandler = $this->createMock(AsanaOAuthHandler::class);
        $mockAuthHandler->expects($this->once())
            ->method('refreshToken')
            ->with($expiredToken)
            ->willThrowException($this->createMock(GuzzleException::class));

        $client = $this->getMockBuilder(AsanaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(AsanaClient::class);
        $authHandlerProperty = $reflection->getProperty('authHandler');
        $authHandlerProperty->setAccessible(true);
        $authHandlerProperty->setValue($client, $mockAuthHandler);

        $accessTokenProperty = $reflection->getProperty('accessToken');
        $accessTokenProperty->setAccessible(true);
        $accessTokenProperty->setValue($client, $expiredToken);

        $this->expectException(TokenInvalidException::class);
        $this->expectExceptionMessage('Error during Refresh token:');

        $client->refreshToken();
    }

    /**
     * Test that refreshToken() throws TokenInvalidException on an IdentityProviderException.
     * @throws MockException
     */
    public function testRefreshTokenThrowsExceptionOnIdentityProviderException(): void
    {
        $expiredToken = $this->createMock(AccessToken::class);
        $expiredToken->method('hasExpired')->willReturn(true);

        $mockAuthHandler = $this->createMock(AsanaOAuthHandler::class);
        $mockAuthHandler->expects($this->once())
            ->method('refreshToken')
            ->with($expiredToken)
            ->willThrowException(new IdentityProviderException('Error', 500, []));

        $client = $this->getMockBuilder(AsanaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(AsanaClient::class);
        $authHandlerProperty = $reflection->getProperty('authHandler');
        $authHandlerProperty->setAccessible(true);
        $authHandlerProperty->setValue($client, $mockAuthHandler);

        $accessTokenProperty = $reflection->getProperty('accessToken');
        $accessTokenProperty->setAccessible(true);
        $accessTokenProperty->setValue($client, $expiredToken);

        $this->expectException(TokenInvalidException::class);
        $this->expectExceptionMessage('Error during Refresh token:');

        $client->refreshToken();
    }

    /**
     * Test that refreshToken() returns null if the token is not expired.
     * @throws MockException|TokenInvalidException
     */
    public function testRefreshTokenReturnsArrayEvenIfTokenNotExpired(): void
    {
        $validToken = $this->createMock(AccessToken::class);
        $validToken->method('hasExpired')->willReturn(false);

        $newAccessToken = new AccessToken(['access_token' => 'refreshed-token']);
        $mockAuthHandler = $this->createMock(AsanaOAuthHandler::class);
        $mockAuthHandler->expects($this->once())
            ->method('refreshToken')
            ->with($validToken)
            ->willReturn($newAccessToken);

        $client = $this->getMockBuilder(AsanaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(AsanaClient::class);
        $authHandlerProperty = $reflection->getProperty('authHandler');
        $authHandlerProperty->setAccessible(true);
        $authHandlerProperty->setValue($client, $mockAuthHandler);

        $accessTokenProperty = $reflection->getProperty('accessToken');
        $accessTokenProperty->setAccessible(true);
        $accessTokenProperty->setValue($client, $validToken);

        $result = $client->refreshToken();

        $this->assertEquals($newAccessToken->jsonSerialize(), $result);
    }
    /**
     * Test handleGuzzleException method with a Guzzle exception having a valid response.
     * @throws ReflectionException|MockException
     */
    public function testHandleGuzzleExceptionWithResponse(): void
    {
        // Create a mock stream that will return the JSON string when cast to string
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('__toString')->willReturn('{"error": "invalid_request"}');

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getStatusCode')->willReturn(400);
        $mockResponse->method('getReasonPhrase')->willReturn('Bad Request');
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getHeaders')->willReturn(['Content-Type' => ['application/json']]);

        $mockGuzzleException = $this->createMock(RequestException::class);
        $mockGuzzleException->method('getResponse')->willReturn($mockResponse);

        $client = $this->getMockBuilder(AsanaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(AsanaClient::class);
        $method = $reflection->getMethod('handleGuzzleException');
        $method->setAccessible(true);

        $this->expectException(OAuthCallbackException::class);

        $data = ['context' => 'Test Context'];
        $method->invokeArgs($client, [$mockGuzzleException, OAuthCallbackException::class, $data]);
    }

    /**
     * Test handleGuzzleException method with a Guzzle exception without a response.
     * @throws ReflectionException|MockException
     */
    public function testHandleGuzzleExceptionWithoutResponse(): void
    {
        $mockGuzzleException = $this->createMock(RequestException::class);
        $mockGuzzleException->method('getResponse')->willReturn(null);

        $client = $this->getMockBuilder(AsanaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(AsanaClient::class);
        $method = $reflection->getMethod('handleGuzzleException');
        $method->setAccessible(true);

        $this->expectException(OAuthCallbackException::class);

        $data = ['context' => 'Test Context'];
        $method->invokeArgs($client, [$mockGuzzleException, OAuthCallbackException::class, $data]);
    }

    /**
     * Test that getSecureAuthorizationUrl() generates a URL with state and PKCE enabled.
     * @throws MockException
     */
    public function testGetSecureAuthorizationUrlWithAllOptions(): void
    {
        $scopes = ['tasks:read', 'projects:write'];
        $expectedOptions = ['scope' => implode(' ', $scopes)];

        $mockedAuthHandler = $this->createMock(AsanaOAuthHandler::class);
        $mockedAuthHandler->expects($this->once())
            ->method('getSecureAuthorizationUrl')
            ->with($expectedOptions, true, true)
            ->willReturn(['url' => 'https://example.com/auth', 'state' => 'test-state', 'codeVerifier' => 'test-pkce']);

        $client = $this->getMockBuilder(AsanaClient::class)
            ->setConstructorArgs(['test-client-id', 'test-client-secret', 'http://redirect.uri', '/token.json'])
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(AsanaClient::class);
        $authHandlerProperty = $reflection->getProperty('authHandler');
        $authHandlerProperty->setAccessible(true);
        $authHandlerProperty->setValue($client, $mockedAuthHandler);

        $result = $client->getSecureAuthorizationUrl($scopes);

        $this->assertSame('https://example.com/auth', $result['url']);
        $this->assertSame('test-state', $result['state']);
        $this->assertSame('test-pkce', $result['codeVerifier']);
    }

    /**
     * Test that getSecureAuthorizationUrl() generates a URL without state.
     * @throws MockException
     */
    public function testGetSecureAuthorizationUrlWithoutState(): void
    {
        $scopes = ['tasks:read'];
        $expectedOptions = ['scope' => implode(' ', $scopes)];

        $mockedAuthHandler = $this->createMock(AsanaOAuthHandler::class);
        $mockedAuthHandler->expects($this->once())
            ->method('getSecureAuthorizationUrl')
            ->with($expectedOptions, false, true)
            ->willReturn(['url' => 'https://example.com/auth', 'state' => null, 'codeVerifier' => 'test-pkce']);

        $client = $this->getMockBuilder(AsanaClient::class)
            ->setConstructorArgs(['test-client-id', 'test-client-secret', 'http://redirect.uri', '/token.json'])
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(AsanaClient::class);
        $authHandlerProperty = $reflection->getProperty('authHandler');
        $authHandlerProperty->setAccessible(true);
        $authHandlerProperty->setValue($client, $mockedAuthHandler);

        $result = $client->getSecureAuthorizationUrl($scopes, false);

        $this->assertSame('https://example.com/auth', $result['url']);
        $this->assertNull($result['state']);
        $this->assertSame('test-pkce', $result['codeVerifier']);
    }

    /**
     * Test that getSecureAuthorizationUrl() generates a URL without PKCE.
     * @throws MockException
     */
    public function testGetSecureAuthorizationUrlWithoutPKCE(): void
    {
        $scopes = ['projects:read'];
        $expectedOptions = ['scope' => implode(' ', $scopes)];

        $mockedAuthHandler = $this->createMock(AsanaOAuthHandler::class);
        $mockedAuthHandler->expects($this->once())
            ->method('getSecureAuthorizationUrl')
            ->with($expectedOptions, true, false)
            ->willReturn(['url' => 'https://example.com/auth', 'state' => 'test-state', 'codeVerifier' => null]);

        $client = $this->getMockBuilder(AsanaClient::class)
            ->setConstructorArgs(['test-client-id', 'test-client-secret', 'http://redirect.uri', '/token.json'])
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(AsanaClient::class);
        $authHandlerProperty = $reflection->getProperty('authHandler');
        $authHandlerProperty->setAccessible(true);
        $authHandlerProperty->setValue($client, $mockedAuthHandler);

        $result = $client->getSecureAuthorizationUrl($scopes, true, false);

        $this->assertSame('https://example.com/auth', $result['url']);
        $this->assertSame('test-state', $result['state']);
        $this->assertNull($result['codeVerifier']);
    }

    /**
     * Test that getSecureAuthorizationUrl() generates a URL without state and PKCE.
     * @throws MockException
     */
    public function testGetSecureAuthorizationUrlWithoutStateAndPKCE(): void
    {
        $scopes = ['users:read'];
        $expectedOptions = ['scope' => implode(' ', $scopes)];

        $mockedAuthHandler = $this->createMock(AsanaOAuthHandler::class);
        $mockedAuthHandler->expects($this->once())
            ->method('getSecureAuthorizationUrl')
            ->with($expectedOptions, false, false)
            ->willReturn(['url' => 'https://example.com/auth', 'state' => null, 'codeVerifier' => null]);

        $client = $this->getMockBuilder(AsanaClient::class)
            ->setConstructorArgs(['test-client-id', 'test-client-secret', 'http://redirect.uri', '/token.json'])
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(AsanaClient::class);
        $authHandlerProperty = $reflection->getProperty('authHandler');
        $authHandlerProperty->setAccessible(true);
        $authHandlerProperty->setValue($client, $mockedAuthHandler);

        $result = $client->getSecureAuthorizationUrl($scopes, false, false);

        $this->assertSame('https://example.com/auth', $result['url']);
        $this->assertNull($result['state']);
        $this->assertNull($result['codeVerifier']);
    }
    /**
     * Test that the withPAT method correctly initializes an AsanaClient instance with a personal access token.
     */
    public function testWithPATInitialization(): void
    {
        $personalAccessToken = 'test-pat';
        $client = AsanaClient::withPAT($personalAccessToken);
        $this->assertSame(AsanaClient::class, get_class($client));
    }

    /**
     * Test that hasToken returns true when accessToken is not null.
     */
    public function testHasTokenReturnsTrue(): void
    {
        $client = AsanaClient::withPAT('test-pat');
        $this->assertTrue($client->hasToken());
    }

    /**
     * Test that hasToken returns false when accessToken is null.
     */
    public function testHasTokenReturnsFalse(): void
    {
        $client = new AsanaClient();
        $this->assertFalse($client->hasToken());
    }

    /**
     * Test that the withPAT method correctly sets the access token.
     * @throws ReflectionException
     */
    public function testWithPATAccessToken(): void
    {
        $personalAccessToken = 'test-pat';

        $client = AsanaClient::withPAT($personalAccessToken);

        $this->assertNotNull($this->getPrivateProperty($client, 'accessToken'));
        $this->assertSame($personalAccessToken, $this->getPrivateProperty($client, 'accessToken')->getToken());
    }

    /**
     * Test that the withPAT method does not initialize authHandler.
     * @throws ReflectionException
     */
    public function testWithPATNoAuthHandler(): void
    {
        $personalAccessToken = 'test-pat';

        $client = AsanaClient::withPAT($personalAccessToken);

        $this->assertNull($this->getPrivateProperty($client, 'authHandler'));
    }

    /**
     * Test that the __construct method initializes authHandler and tokenStoragePath correctly.
     * @throws ReflectionException
     */
    public function testConstructorInitializesAuthHandlerAndTokenStoragePath(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $redirectUri = 'http://redirect.uri';
        $tokenStoragePath = '/tmp/token.json';

        $client = new AsanaClient($clientId, $clientSecret, $redirectUri, $tokenStoragePath);

        $this->assertNotNull($this->getPrivateProperty($client, 'authHandler'));
        $this->assertSame($tokenStoragePath, $this->getPrivateProperty($client, 'tokenStoragePath'));
    }

    /**
     * Test that the default tokenStoragePath is set to "token.json" when none is provided.
     * @throws ReflectionException
     */
    public function testConstructorSetsDefaultTokenStoragePath(): void
    {
        $client = new AsanaClient();

        $expectedPath = getcwd() . '/token.json';
        $this->assertSame($expectedPath, $this->getPrivateProperty($client, 'tokenStoragePath'));
    }

    /**
     * Test that authHandler is not initialized when clientId and clientSecret are not provided.
     * @throws ReflectionException
     */
    public function testConstructorNoAuthWhenClientIdAndSecretNotProvided(): void
    {
        $client = new AsanaClient();

        $this->assertNull($this->getPrivateProperty($client, 'authHandler'));
    }

    /**
     * Helper method to access private/protected properties via reflection.
     *
     * @param object $object
     * @param string $propertyName
     * @return mixed
     * @throws ReflectionException
     */
    private function getPrivateProperty(object $object, string $propertyName)
    {
        $reflectionClass = new ReflectionClass($object);
        $property = $reflectionClass->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * Test that saveToken writes the access token to the specified storage file.
     */
    public function testSaveTokenWritesAccessTokenToFile(): void
    {
        $password = 'test_password';
        $tokenData = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];
        $tokenStoragePath = $this->tempDir . '/saved_token.json';

        $client = new AsanaClient(null, null, null, $tokenStoragePath);

        // Set access token via reflection
        $reflection = new ReflectionClass(AsanaClient::class);
        $accessTokenProperty = $reflection->getProperty('accessToken');
        $accessTokenProperty->setAccessible(true);
        $accessTokenProperty->setValue($client, new AccessToken($tokenData));

        $client->saveToken($password);

        $this->assertFileExists($tokenStoragePath);
        $storedTokenData = json_decode(file_get_contents($tokenStoragePath), true);

        // Decrypt for verification
        $decrypted = [
            'access_token' => CryptoUtils::decrypt($storedTokenData['access_token'], $password),
            'refresh_token' => CryptoUtils::decrypt($storedTokenData['refresh_token'], $password),
            'expires' => $storedTokenData['expires'],
        ];
        $this->assertSame($tokenData, $decrypted);
    }
    /**
     * Test that saveToken does not create or write to a file when accessToken is null.
     */
    public function testSaveTokenDoesNothingIfTokenIsNull(): void
    {
        $password = 'test_password';
        $tokenStoragePath = $this->tempDir . '/null_token.json';
        $client = new AsanaClient(null, null, null, $tokenStoragePath);

        $client->saveToken($password);

        $this->assertFileDoesNotExist($tokenStoragePath);
    }

    /**
     * Test that the token stored by saveToken matches the attributes of the access token.
     */
    public function testSaveTokenMatchesAccessTokenAttributes(): void
    {
        $password = 'test_password';
        $tokenData = [
            'access_token' => 'sample-access-token',
            'refresh_token' => 'sample-refresh-token',
            'expires' => time() + 7200,
        ];
        $tokenStoragePath = $this->tempDir . '/match_token.json';

        $client = new AsanaClient(null, null, null, $tokenStoragePath);

        // Get the reflection property and set its value
        $reflection = new ReflectionClass(AsanaClient::class);
        $accessTokenProperty = $reflection->getProperty('accessToken');
        $accessTokenProperty->setAccessible(true);
        $accessTokenProperty->setValue($client, new AccessToken($tokenData));

        $client->saveToken($password);

        $this->assertFileExists($tokenStoragePath);
        $storedTokenData = json_decode(file_get_contents($tokenStoragePath), true);
        $decrypted = [
            'access_token' => CryptoUtils::decrypt($storedTokenData['access_token'], $password),
            'refresh_token' => CryptoUtils::decrypt($storedTokenData['refresh_token'], $password),
            'expires' => $storedTokenData['expires'],
        ];
        $this->assertSame($tokenData, $decrypted);
    }

    /**
     * Test that the getAuthorizationUrl() method returns the correct authorization
     * URL as provided by the OAuth handler.
     *
     * @return void
     * @throws MockException
     */
    public function testGetAuthorizationUrlReturnsCorrectUrl(): void
    {
        $scopes = ['openid', 'profile'];
        $expectedOptions = ['scope' => implode(' ', $scopes)];

        // Create mock auth handler that will return a specific URL
        $mockedAuthHandler = $this->createMock(AsanaOAuthHandler::class);
        $mockedAuthHandler->expects($this->once())
            ->method('getAuthorizationUrl')
            ->with($expectedOptions)
            ->willReturn('https://example.com/oauth/authorize');

        // Create a partial mock of AsanaClient, with real implementation of getAuthorizationUrl
        $client = $this->getMockBuilder(AsanaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods([]) // Don't mock any methods - use real implementations
            ->getMock();

        // Set the mocked auth handler on the client
        $reflection = new ReflectionClass(AsanaClient::class);
        $authHandlerProperty = $reflection->getProperty('authHandler');
        $authHandlerProperty->setAccessible(true);
        $authHandlerProperty->setValue($client, $mockedAuthHandler);

        // Call the method we're testing
        $result = $client->getAuthorizationUrl($scopes);

        // Assert the result
        $this->assertSame('https://example.com/oauth/authorize', $result);
    }

    /**
     * Test that withAccessToken() correctly initializes an AsanaClient instance with the provided access token.
     * @throws ReflectionException
     */
    public function testWithAccessTokenInitializesAccessTokenCorrectly(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $this->assertNotNull($this->getPrivateProperty($client, 'accessToken'));
        $this->assertSame($tokenArray['access_token'], $this->getPrivateProperty($client, 'accessToken')->getToken());
    }

    /**
     * Test that the withAccessToken method initializes the authHandler correctly.
     * @throws ReflectionException
     */
    public function testWithAccessTokenInitializesAuthHandler(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $this->assertNotNull($this->getPrivateProperty($client, 'authHandler'));
    }
    /**
     * Test that the tasks() method initializes and returns the TaskApiService instance.
     * @throws TokenInvalidException
     */
    public function testTasksMethodInitializesTaskApiService(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $this->assertInstanceOf(TaskApiService::class, $client->tasks());
    }

    /**
     * Test that the tasks() method stores the initialized TaskApiService instance for subsequent calls.
     * @throws TokenInvalidException
     */
    public function testTasksMethodStoresInitializedTaskApiService(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $firstCall = $client->tasks();
        $secondCall = $client->tasks();

        $this->assertSame($firstCall, $secondCall);
    }

    /**
     * Test that the projects() method initializes and returns the ProjectApiService instance.
     * @throws TokenInvalidException
     */
    public function testProjectsMethodInitializesProjectApiService(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $this->assertInstanceOf(ProjectApiService::class, $client->projects());
    }

    /**
     * Test that the projects() method stores the initialized ProjectApiService instance for subsequent calls.
     * @throws TokenInvalidException
     */
    public function testProjectsMethodStoresInitializedProjectApiService(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $firstCall = $client->projects();
        $secondCall = $client->projects();

        $this->assertSame($firstCall, $secondCall);
    }

    /**
     * Test that the users() method initializes and returns the UserApiService instance.
     * @throws TokenInvalidException
     */
    public function testUsersMethodInitializesUserApiService(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $this->assertInstanceOf(UserApiService::class, $client->users());
    }

    /**
     * Test that the tags() method initializes and returns the TagsApiService instance.
     * @throws TokenInvalidException
     */
    public function testTagsMethodInitializesTagsApiService(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $this->assertInstanceOf(TagsApiService::class, $client->tags());
    }

    /**
     * Test that the tags() method stores the initialized TagsApiService instance for subsequent calls.
     * @throws TokenInvalidException
     */
    public function testTagsMethodStoresInitializedTagsApiService(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $firstCall = $client->tags();
        $secondCall = $client->tags();

        $this->assertSame($firstCall, $secondCall);
    }

    /**
     * Test that the users() method stores the initialized UserApiService instance for subsequent calls.
     * @throws TokenInvalidException
     */
    public function testUsersMethodStoresInitializedUserApiService(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $firstCall = $client->users();
        $secondCall = $client->users();

        $this->assertSame($firstCall, $secondCall);
    }

    /**
     * Test that the sections() method initializes and returns the SectionApiService instance.
     * @throws TokenInvalidException
     */
    public function testSectionsMethodInitializesSectionApiService(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $this->assertInstanceOf(SectionApiService::class, $client->sections());
    }

    /**
     * Test that the sections() method stores the initialized SectionApiService instance for subsequent calls.
     * @throws TokenInvalidException
     */
    public function testSectionsMethodStoresInitializedSectionApiService(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $firstCall = $client->sections();
        $secondCall = $client->sections();

        $this->assertSame($firstCall, $secondCall);
    }

    /**
     * Test that the memberships() method initializes and returns the MembershipApiService instance.
     * @throws TokenInvalidException
     */
    public function testMembershipsMethodInitializesMembershipApiService(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $this->assertInstanceOf(MembershipApiService::class, $client->memberships());
    }

    /**
     * Test that the customFields() method initializes and returns the CustomFieldApiService instance.
     * @throws TokenInvalidException
     */
    public function testCustomFieldsMethodInitializesCustomFieldApiService(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $this->assertInstanceOf(CustomFieldApiService::class, $client->customFields());
    }

    /**
     * Test that the customFields() method stores the initialized CustomFieldApiService instance for subsequent calls.
     * @throws TokenInvalidException
     */
    public function testCustomFieldsMethodStoresInitializedCustomFieldApiService(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $firstCall = $client->customFields();
        $secondCall = $client->customFields();

        $this->assertSame($firstCall, $secondCall);
    }

    /**
     * Test that the memberships() method stores the initialized MembershipApiService instance for subsequent calls.
     * @throws TokenInvalidException
     */
    public function testMembershipsMethodStoresInitializedMembershipApiService(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $firstCall = $client->memberships();
        $secondCall = $client->memberships();

        $this->assertSame($firstCall, $secondCall);
    }

    /**
     * Test that the attachments() method initializes and returns the AttachmentApiService instance.
     * @throws TokenInvalidException
     */
    public function testAttachmentsMethodInitializesAttachmentApiService(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $this->assertInstanceOf(AttachmentApiService::class, $client->attachments());
    }

    /**
     * Test that the attachments() method stores the initialized AttachmentApiService instance for subsequent calls.
     * @throws TokenInvalidException
     */
    public function testAttachmentsMethodStoresInitializedAttachmentApiService(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $firstCall = $client->attachments();
        $secondCall = $client->attachments();

        $this->assertSame($firstCall, $secondCall);
    }

    /**
     * Test that the workspaces() method initializes and returns the WorkspaceApiService instance.
     * @throws TokenInvalidException
     */
    public function testWorkspacesMethodInitializesWorkspaceApiService(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $this->assertInstanceOf(WorkspaceApiService::class, $client->workspaces());
    }

    /**
     * Test that the workspaces() method stores the initialized WorkspaceApiService instance for subsequent calls.
     * @throws TokenInvalidException
     */
    public function testWorkspacesMethodStoresInitializedWorkspaceApiService(): void
    {
        $clientId = 'test-client-id';
        $clientSecret = 'test-client-secret';
        $tokenArray = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires' => time() + 3600,
        ];

        $client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenArray);

        $firstCall = $client->workspaces();
        $secondCall = $client->workspaces();

        $this->assertSame($firstCall, $secondCall);
    }

    /**
     * Test that handleCallback successfully retrieves an access token.
     * @throws OAuthCallbackException
     * @throws MockException
     */
    public function testHandleCallbackSuccessful(): void
    {
        $authorizationCode = 'auth-code';
        $codeVerifier = 'pkce-verifier';
        $mockedAuthHandler = $this->createMock(AsanaOAuthHandler::class);
        $mockedAccessToken = $this->createMock(AccessToken::class);
        $mockedAccessToken->method('jsonSerialize')->willReturn(['access_token' => 'test-token']);

        $mockedAuthHandler->expects($this->once())
            ->method('handleCallback')
            ->with($authorizationCode, $codeVerifier)
            ->willReturn($mockedAccessToken);

        $client = $this->getMockBuilder(AsanaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(AsanaClient::class);
        $authHandlerProperty = $reflection->getProperty('authHandler');
        $authHandlerProperty->setAccessible(true);
        $authHandlerProperty->setValue($client, $mockedAuthHandler);

        $result = $client->handleCallback($authorizationCode, $codeVerifier);
        $this->assertSame(['access_token' => 'test-token'], $result);
    }

    /**
     * Test handleCallback method throws OAuthCallbackException on GuzzleException.
     * @throws MockException
     */
    public function testHandleCallbackWithGuzzleException(): void
    {
        $authorizationCode = 'auth-code';
        $codeVerifier = 'pkce-verifier';
        $mockedAuthHandler = $this->createMock(AsanaOAuthHandler::class);
        $mockedAuthHandler->expects($this->once())
            ->method('handleCallback')
            ->with($authorizationCode, $codeVerifier)
            ->willThrowException($this->createMock(GuzzleException::class));

        $client = $this->getMockBuilder(AsanaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(AsanaClient::class);
        $authHandlerProperty = $reflection->getProperty('authHandler');
        $authHandlerProperty->setAccessible(true);
        $authHandlerProperty->setValue($client, $mockedAuthHandler);

        $this->expectException(OAuthCallbackException::class);
        $client->handleCallback($authorizationCode, $codeVerifier);
    }

    /**
     * Test handleCallback method throws OAuthCallbackException on general exceptions.
     * @throws MockException
     */
    public function testHandleCallbackWithGeneralException(): void
    {
        $authorizationCode = 'auth-code';
        $codeVerifier = 'pkce-verifier';
        $mockedAuthHandler = $this->createMock(AsanaOAuthHandler::class);
        $mockedAuthHandler->expects($this->once())
            ->method('handleCallback')
            ->with($authorizationCode, $codeVerifier)
            ->willThrowException(new Exception('A general error occurred'));

        $client = $this->getMockBuilder(AsanaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(AsanaClient::class);
        $authHandlerProperty = $reflection->getProperty('authHandler');
        $authHandlerProperty->setAccessible(true);
        $authHandlerProperty->setValue($client, $mockedAuthHandler);

        $this->expectException(OAuthCallbackException::class);
        $this->expectExceptionMessage('A general error occurred');
        $client->handleCallback($authorizationCode, $codeVerifier);
    }
    /**
     * Test that handleGeneralException correctly throws the specified custom exception.
     *
     * @throws ReflectionException
     */
    public function testHandleGeneralExceptionWithCustomException(): void
    {
        $mockException = new Exception('Test exception message', 500);

        $client = $this->getMockBuilder(AsanaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(AsanaClient::class);
        $method = $reflection->getMethod('handleGeneralException');
        $method->setAccessible(true);

        $exceptionClassToThrow = OAuthCallbackException::class;

        $this->expectException(OAuthCallbackException::class);
        $this->expectExceptionMessage('Error during test-context: Test exception message');
        $this->expectExceptionCode(500);

        $method->invokeArgs($client, [
            $mockException,
            $exceptionClassToThrow,
            ['context' => 'test-context']
        ]);
    }

    /**
     * Test that logout clears accessToken and apiClient.
     */
    public function testLogoutClearsAccessTokenAndApiClient(): void
    {
        $client = AsanaClient::withPAT('test-pat');

        $reflection = new ReflectionClass(AsanaClient::class);

        $accessTokenProperty = $reflection->getProperty('accessToken');
        $accessTokenProperty->setAccessible(true);
        $this->assertNotNull($accessTokenProperty->getValue($client));

        $apiClientProperty = $reflection->getProperty('apiClient');
        $apiClientProperty->setAccessible(true);
        $this->assertNull($apiClientProperty->getValue($client));

        $client->logout();

        $this->assertNull($accessTokenProperty->getValue($client));
        $this->assertNull($apiClientProperty->getValue($client));
    }

    /**
     * Test that logout deletes the token storage file if it exists.
     */
    public function testLogoutDeletesTokenFile(): void
    {
        $tokenStoragePath = $this->tempDir . '/test_token.json';
        file_put_contents($tokenStoragePath, json_encode(['test' => 'data']));

        $client = new AsanaClient(null, null, null, $tokenStoragePath);

        $this->assertFileExists($tokenStoragePath);

        $client->logout();

        $this->assertFileDoesNotExist($tokenStoragePath);
    }

    /**
     * Test that logout does not throw an error if the token file does not exist.
     */
    public function testLogoutDoesNotThrowErrorIfTokenFileDoesNotExist(): void
    {
        $tokenStoragePath = $this->tempDir . '/non_existing_token.json';

        $client = new AsanaClient(null, null, null, $tokenStoragePath);

        $this->assertFileDoesNotExist($tokenStoragePath);

        $success = false;
        try {
            $client->logout();
            $success = true; // Logout succeeded without throwing
        } catch (Exception $e) {
        }

        $this->assertTrue($success, 'Logout should not throw for non-existing token file');
    }

    /**
     * Test that ensureValidToken throws TokenInvalidException if no access token is available.
     */
    public function testEnsureValidTokenThrowsExceptionIfNoToken(): void
    {
        $client = new AsanaClient();

        $this->expectException(TokenInvalidException::class);
        $this->expectExceptionMessage('No access token is available.');

        $client->ensureValidToken();
    }

    /**
     * Test that getApiClient initializes AsanaApiClient when it does not exist.
     * @throws ReflectionException
     */
    public function testGetApiClientInitializesApiClient(): void
    {
        $token = 'test-access-token';
        $client = AsanaClient::withPAT($token);

        $reflection = new ReflectionClass(AsanaClient::class);
        $apiClientProperty = $reflection->getProperty('apiClient');
        $apiClientProperty->setAccessible(true);

        // Ensure apiClient is null initially
        $this->assertNull($apiClientProperty->getValue($client));

        // Call getApiClient to initialize apiClient
        $apiClient = $reflection->getMethod('getApiClient');
        $apiClient->setAccessible(true);
        $result = $apiClient->invoke($client);

        $this->assertInstanceOf(AsanaApiClient::class, $result);
        $this->assertSame($result, $apiClientProperty->getValue($client));
    }

    /**
     * Test that getApiClient returns existing AsanaApiClient instance if one already exists.
     * @throws ReflectionException|MockException
     */
    public function testGetApiClientReturnsExistingApiClient(): void
    {
        $token = 'test-access-token';
        $mockApiClient = $this->createMock(AsanaApiClient::class);
        $client = AsanaClient::withPAT($token);

        $reflection = new ReflectionClass(AsanaClient::class);
        $apiClientProperty = $reflection->getProperty('apiClient');
        $apiClientProperty->setAccessible(true);
        $apiClientProperty->setValue($client, $mockApiClient);

        // Call getApiClient and ensure the existing apiClient is returned
        $apiClient = $reflection->getMethod('getApiClient');
        $apiClient->setAccessible(true);
        $result = $apiClient->invoke($client);

        $this->assertSame($mockApiClient, $result);
    }

    /**
     * Test that getApiClient throws TokenInvalidException when access token is invalid.
     * @throws ReflectionException
     */
    public function testGetApiClientThrowsTokenInvalidExceptionWithoutToken(): void
    {
        $client = new AsanaClient();

        $this->expectException(TokenInvalidException::class);
        $this->expectExceptionMessage('No access token is available.');

        $reflection = new ReflectionClass(AsanaClient::class);
        $apiClientMethod = $reflection->getMethod('getApiClient');
        $apiClientMethod->setAccessible(true);

        $apiClientMethod->invoke($client);
    }

    /**
     * Test that ensureValidToken returns true if token has no expiration (e.g., PAT).
     * @throws TokenInvalidException
     */
    public function testEnsureValidTokenReturnsTrueForNonExpiringToken(): void
    {
        $client = AsanaClient::withPAT('test-pat');

        $this->assertTrue($client->ensureValidToken());
    }

    /**
     * Test that ensureValidToken throws TokenInvalidException if the token is expired
     * and cannot be refreshed due to a GuzzleException.
     * @throws MockException
     */
    public function testEnsureValidTokenThrowsExceptionOnGuzzleErrorDuringRefresh(): void
    {
        $expiredToken = $this->createMock(AccessToken::class);
        $expiredToken->method('hasExpired')->willReturn(true);
        $expiredToken->method('getExpires')->willReturn(time() - 3600); // Already expired.

        $mockedAuthHandler = $this->createMock(AsanaOAuthHandler::class);
        $mockedAuthHandler->expects($this->once())
            ->method('refreshToken')
            ->with($expiredToken)
            ->willThrowException($this->createMock(GuzzleException::class));

        $client = $this->getMockBuilder(AsanaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(AsanaClient::class);
        $authHandlerProperty = $reflection->getProperty('authHandler');
        $authHandlerProperty->setAccessible(true);
        $authHandlerProperty->setValue($client, $mockedAuthHandler);

        $accessTokenProperty = $reflection->getProperty('accessToken');
        $accessTokenProperty->setAccessible(true);
        $accessTokenProperty->setValue($client, $expiredToken);

        $this->expectException(TokenInvalidException::class);
        $this->expectExceptionMessage('Error during Refresh token:');

        $client->ensureValidToken();
    }

    /**
     * Test that ensureValidToken throws TokenInvalidException if a general exception occurs during token refresh.
     * @throws MockException
     */
    public function testEnsureValidTokenThrowsExceptionOnGeneralErrorDuringRefresh(): void
    {
        $expiredToken = $this->createMock(AccessToken::class);
        $expiredToken->method('hasExpired')->willReturn(true);
        $expiredToken->method('getExpires')->willReturn(time() - 3600); // Already expired.

        $mockedAuthHandler = $this->createMock(AsanaOAuthHandler::class);
        $mockedAuthHandler->expects($this->once())
            ->method('refreshToken')
            ->with($expiredToken)
            ->willThrowException(new Exception('General error occurred'));

        $client = $this->getMockBuilder(AsanaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(AsanaClient::class);
        $authHandlerProperty = $reflection->getProperty('authHandler');
        $authHandlerProperty->setAccessible(true);
        $authHandlerProperty->setValue($client, $mockedAuthHandler);

        $accessTokenProperty = $reflection->getProperty('accessToken');
        $accessTokenProperty->setAccessible(true);
        $accessTokenProperty->setValue($client, $expiredToken);

        $this->expectException(TokenInvalidException::class);
        $this->expectExceptionMessage('Error during Refresh token: General error occurred');

        $client->ensureValidToken();
    }

    /**
     * Test that ensureValidToken returns true for an OAuth token with no expiration.
     * @throws TokenInvalidException
     * @throws MockException
     */
    public function testEnsureValidTokenReturnsTrueForOAuthTokenWithNoExpiration(): void
    {
        $tokenWithoutExpiration = $this->createMock(AccessToken::class);
        $tokenWithoutExpiration->method('getExpires')->willReturn(null);

        $client = $this->getMockBuilder(AsanaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(AsanaClient::class);
        $accessTokenProperty = $reflection->getProperty('accessToken');
        $accessTokenProperty->setAccessible(true);
        $accessTokenProperty->setValue($client, $tokenWithoutExpiration);

        $this->assertTrue($client->ensureValidToken());
    }

    /**
     * Test that ensureValidToken refreshes the token if expired.
     * @throws TokenInvalidException|MockException
     */
    public function testEnsureValidTokenSuccessfullyRefreshesToken(): void
    {
        $expiredToken = $this->createMock(AccessToken::class);
        $expiredToken->method('hasExpired')->willReturn(true);
        // Add this line to ensure the token is treated as having an expiration time
        $expiredToken->method('getExpires')->willReturn(time() - 3600); // Expired 1 hour ago

        $mockedAuthHandler = $this->createMock(AsanaOAuthHandler::class);
        $mockedAuthHandler->expects($this->once())
            ->method('refreshToken')
            ->with($expiredToken)
            ->willReturn(new AccessToken(['access_token' => 'new-token']));

        $client = $this->getMockBuilder(AsanaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(AsanaClient::class);
        $authHandlerProperty = $reflection->getProperty('authHandler');
        $authHandlerProperty->setAccessible(true);
        $authHandlerProperty->setValue($client, $mockedAuthHandler);

        $accessTokenProperty = $reflection->getProperty('accessToken');
        $accessTokenProperty->setAccessible(true);
        $accessTokenProperty->setValue($client, $expiredToken);

        $this->assertTrue($client->ensureValidToken());
    }

    /**
     * Test that handleGeneralException handles Throwable exceptions correctly.
     *
     * @throws ReflectionException|MockException
     */
    public function testHandleGeneralExceptionWithThrowable(): void
    {
        $mockException = new Exception('Throwable exception message', 404);

        $client = $this->getMockBuilder(AsanaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(AsanaClient::class);
        $method = $reflection->getMethod('handleGeneralException');
        $method->setAccessible(true);

        $exceptionClassToThrow = TokenInvalidException::class;

        $this->expectException(TokenInvalidException::class);
        $this->expectExceptionMessage('Error during test-context: Throwable exception message');
        $this->expectExceptionCode(404);

        $method->invokeArgs($client, [
            $mockException,
            $exceptionClassToThrow,
            ['context' => 'test-context']
        ]);
    }
}
