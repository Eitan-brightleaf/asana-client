<?php

namespace BrightleafDigital;

use BrightleafDigital\Api\AttachmentApiService;
use BrightleafDigital\Api\CustomFieldApiService;
use BrightleafDigital\Api\MembershipApiService;
use BrightleafDigital\Api\ProjectApiService;
use BrightleafDigital\Api\SectionApiService;
use BrightleafDigital\Api\TagsApiService;
use BrightleafDigital\Api\TaskApiService;
use BrightleafDigital\Api\UserApiService;
use BrightleafDigital\Api\WorkspaceApiService;
use BrightleafDigital\Auth\AsanaOAuthHandler;
use BrightleafDigital\Exceptions\TokenInvalidException;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Exceptions\OAuthCallbackException;
use BrightleafDigital\Utils\CryptoUtils;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Throwable;

class AsanaClient
{
    /**
     * OAuth handler for authentication with Asana API
     * @var ?AsanaOAuthHandler
     */
    private ?AsanaOAuthHandler $authHandler = null;

    /**
     * HTTP client for making API requests to Asana
     * @var AsanaApiClient|null
     */
    private ?AsanaApiClient $apiClient = null;

    /**
     * Current access token for authentication
     * @var AccessToken|null
     */
    private ?AccessToken $accessToken = null;

    /**
     * File path for token storage
     * @var string
     */
    private string $tokenStoragePath;

    /**
     * Tasks API service instance
     * @var TaskApiService|null
     */
    private ?TaskApiService $tasks = null;

    /**
     * Projects API service instance
     * @var ProjectApiService|null
     */
    private ?ProjectApiService $projects = null;

    /**
     * Users API service instance
     * @var UserApiService|null
     */
    private ?UserApiService $users = null;

    /**
     * Tags API service instance
     * @var TagsApiService|null
     */
    private ?TagsApiService $tags = null;

    /**
     * Sections API service instance
     * @var SectionApiService|null
     */
    private ?SectionApiService $sections = null;

    /**
     * Memberships API service instance
     * @var MembershipApiService|null
     */
    private ?MembershipApiService $memberships = null;

    /**
     * Attachments API service instance
     * @var AttachmentApiService|null
     */
    private ?AttachmentApiService $attachments = null;
    /**
     * Workspaces API service instance
     * @var WorkspaceApiService|null
     */
    private ?WorkspaceApiService $workspaces = null;
    /**
     * Custom Fields API service instance
     * @var CustomFieldApiService|null
     */
    private ?CustomFieldApiService $customFields = null;
    /**
    * List of callbacks to be triggered when the access token is refreshed.
    * The array can have numeric or string keys, which are used to identify the callbacks.
    * Each callback should accept one parameter: the refreshed access token.
    * @var array<string|int, callable>
    */
    private array $tokenRefreshSubscribers = [];

    /**
     * Constructor method for initializing the AsanaOAuthHandler and setting the token storage path.
     *
     * @param string|null $clientId Optional client ID for authentication.
     * @param string|null $clientSecret Optional client secret for authentication.
     * @param string|null $redirectUri Optional redirect URI for OAuth flow.
     * @param string|null $tokenStoragePath Path to token storage file. Defaults to token.json in working dir.
     */
    public function __construct(
        ?string $clientId = null,
        ?string $clientSecret = null,
        ?string $redirectUri = null,
        ?string $tokenStoragePath = null
    ) {
        if ($clientId && $clientSecret) {
            $this->authHandler = new AsanaOAuthHandler($clientId, $clientSecret, $redirectUri);
        }

        $this->tokenStoragePath = $tokenStoragePath ?? getcwd() . '/token.json';
    }

    /**
     * Constructor method for initializing the AsanaOAuthHandler and setting the token storage path.
     *
     * @param string|null $clientId Optional client ID for authentication.
     * @param string|null $clientSecret Optional client secret for authentication.
     * @param string|null $redirectUri Optional redirect URI for OAuth flow.
     * @param string|null $tokenStoragePath Path to token storage file. Defaults to token.json in working dir.
     */
    public static function OAuth(
        ?string $clientId = null,
        ?string $clientSecret = null,
        ?string $redirectUri = null,
        ?string $tokenStoragePath = null
    ): self {
        return new self($clientId, $clientSecret, $redirectUri, $tokenStoragePath);
    }

    /**
     * Initialize the Asana client with an access token
     *
     * @param string $clientId OAuth client ID
     * @param string $clientSecret OAuth client secret
     * @param array $token The user's preexisting access token
     * @param string|null $tokenStoragePath Path to token storage file. Defaults to token.json in working dir.
     * @return self
     */
    public static function withAccessToken(
        string $clientId,
        string $clientSecret,
        array $token,
        ?string $tokenStoragePath = null
    ): self {
        $instance = new self(
            $clientId,
            $clientSecret,
            '', // No redirect URI required when preloading a token
            $tokenStoragePath
        );
        $instance->accessToken = new AccessToken($token);
        return $instance;
    }

    /**
     * Initialize the Asana client with a Personal Access Token (PAT)
     *
     * @param string $personalAccessToken The user's PAT from Asana
     * @param string|null $tokenStoragePath Path to token storage file. Defaults to token.json in working dir.
     *
     * @return self
     */
    public static function withPAT(
        string $personalAccessToken,
        ?string $tokenStoragePath = null
    ): self {
        $instance = new self('', '', '', $tokenStoragePath); // clientId, clientSecret, & redirectUri not needed for PAT
        $instance->accessToken = new AccessToken(['access_token' => $personalAccessToken]);
        return $instance;
    }

    /**
     * Retrieve the Task API service instance.
     *
     * Initializes the Task API service if it is not already set, ensuring
     * the API client is configured, and validates the current token.
     *
     * @return TaskApiService The instance of TaskApiService.
     * @throws TokenInvalidException If no token or it's expired and error refreshing it.
     */
    public function tasks(): TaskApiService
    {
        if ($this->tasks === null) {
            $this->tasks = new TaskApiService($this->getApiClient());
            return $this->tasks;
        }

        $this->ensureValidToken();

        return $this->tasks;
    }

    /**
     * Retrieve the ProjectApiService instance. If it does not exist, it creates and initializes it.
     * Ensures the token validity before returning the instance.
     *
     * @return ProjectApiService The initialized ProjectApiService instance.
     * @throws TokenInvalidException If no token or it's expired and error refreshing it.
     */
    public function projects(): ProjectApiService
    {
        if ($this->projects === null) {
            $this->projects = new ProjectApiService($this->getApiClient());
            return $this->projects;
        }

        $this->ensureValidToken();

        return $this->projects;
    }

    /**
     * Retrieve the UserApiService instance. If it does not exist, it creates and initializes it.
     * Ensures the token validity before returning the instance.
     *
     * @return UserApiService The initialized UserApiService instance.
     * @throws TokenInvalidException If no token or it's expired and error refreshing it.
     */
    public function users(): UserApiService
    {
        if ($this->users === null) {
            $this->users = new UserApiService($this->getApiClient());
            return $this->users;
        }

        $this->ensureValidToken();

        return $this->users;
    }

    /**
     * Retrieve the TagsApiService instance. If it does not exist, it creates and initializes it.
     * Ensures the token validity before returning the instance.
     *
     * @return TagsApiService The initialized TagsApiService instance.
     * @throws TokenInvalidException If no token or it's expired and error refreshing it.
     */
    public function tags(): TagsApiService
    {
        if ($this->tags === null) {
            $this->tags = new TagsApiService($this->getApiClient());
            return $this->tags;
        }

        $this->ensureValidToken();

        return $this->tags;
    }

    /**
     * Retrieve the SectionApiService instance. If it does not exist, it creates and initializes it.
     * Ensures the token validity before returning the instance.
     *
     * @return SectionApiService The initialized SectionApiService instance.
     * @throws TokenInvalidException If no token or it's expired and error refreshing it.
     */
    public function sections(): SectionApiService
    {
        if ($this->sections === null) {
            $this->sections = new SectionApiService($this->getApiClient());
            return $this->sections;
        }

        $this->ensureValidToken();

        return $this->sections;
    }

    /**
     * Retrieve the Membership API service instance. If it does not exist, it creates and initializes it.
     * Ensures the token validity before returning the instance.
     *
     * @return MembershipApiService The initialized MembershipApiService instance.
     * @throws TokenInvalidException If no token or it's expired and error refreshing it.
     */
    public function memberships(): MembershipApiService
    {
        if ($this->memberships === null) {
            $this->memberships = new MembershipApiService($this->getApiClient());
            return $this->memberships;
        }

        $this->ensureValidToken();

        return $this->memberships;
    }

    /**
     * Retrieve the AttachmentApiService instance. If it does not exist, it creates and initializes it.
     * Ensures the token validity before returning the instance.
     *
     * @return AttachmentApiService The initialized AttachmentApiService instance.
     * @throws TokenInvalidException If no token or it's expired and error refreshing it.
     */
    public function attachments(): AttachmentApiService
    {
        if ($this->attachments === null) {
            $this->attachments = new AttachmentApiService($this->getApiClient());
            return $this->attachments;
        }

        $this->ensureValidToken();

        return $this->attachments;
    }

    /**
     * Retrieves the Workspace API service instance.
     *
     * @return WorkspaceApiService Returns the Workspace API service instance.
     * @throws TokenInvalidException If the access token is invalid.
     */
    public function workspaces(): WorkspaceApiService
    {
        if ($this->workspaces === null) {
            $this->workspaces = new WorkspaceApiService($this->getApiClient());
            return $this->workspaces;
        }
        $this->ensureValidToken();
        return $this->workspaces;
    }

    /**
     * Provides access to the custom fields API service.
     *
     * @return CustomFieldApiService Returns the instance of the custom fields API service.
     * @throws TokenInvalidException If the token is invalid or cannot be refreshed.
     */
    public function customFields(): CustomFieldApiService
    {
        if ($this->customFields === null) {
            $this->customFields = new CustomFieldApiService($this->getApiClient());
            return $this->customFields;
        }
        $this->ensureValidToken();
        return $this->customFields;
    }

    /**
     * Get the authorization URL for OAuth flow
     *
     * @return string
     */
    public function getAuthorizationUrl(array $scopes): string
    {
        $options['scope'] = implode(' ', $scopes);
        return $this->authHandler->getAuthorizationUrl($options);
    }

    /**
     * Get authorization URL, state, and PKCE verifier
     *
     * @param bool $enableState
     * @param bool $enablePKCE
     * @return array ['url' => string, 'state' => string|null, 'codeVerifier' => string|null]
     */
    public function getSecureAuthorizationUrl(array $scopes, bool $enableState = true, bool $enablePKCE = true): array
    {
        $options['scope'] = implode(' ', $scopes);
        return $this->authHandler->getSecureAuthorizationUrl($options, $enableState, $enablePKCE);
    }


   /**
    * Handle callback and retrieve an access token.
    *
    * @param string $authorizationCode The code returned by the OAuth callback.
    * @param string|null $codeVerifier The PKCE code verifier (optional).
    *
    * @return array Access Token data as array.
    *
    * @throws OAuthCallbackException If the callback handling process fails.
    */
    public function handleCallback(string $authorizationCode, ?string $codeVerifier = null): ?array
    {
        try {
            $this->accessToken = $this->authHandler->handleCallback($authorizationCode, $codeVerifier);
            return $this->accessToken->jsonSerialize();
        } catch (GuzzleException $e) {
            $data = [
                'authorization_code' => substr($authorizationCode, 0, 5) . '***' . substr($authorizationCode, - 5),
                'code_verifier'      => isset($codeVerifier) ? 'Provided' : 'Not Provided',
                'context'            => 'OAuth callback'
            ];
            $this->handleGuzzleException($e, OAuthCallbackException::class, $data);
        } catch (Exception $e) {
            $data = [
                'authorization_code' => substr($authorizationCode, 0, 5) . '***' . substr($authorizationCode, - 5),
                'code_verifier'      => isset($codeVerifier) ? 'Provided' : 'Not Provided',
                'context'            => 'OAuth callback'
            ];
            $this->handleGeneralException($e, OAuthCallbackException::class, $data);
        }
        // @codeCoverageIgnoreStart
        // Unreachable code. Just to satisfy return type.
        return null;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Handles exceptions raised by Guzzle HTTP client and extracts relevant response data.
     *
     * @param GuzzleException $exceptionThrown The exception thrown by the Guzzle HTTP client.
     * @param string $exceptionToThrow
     * @param array $data Additional contextual data related to the request.
     *
     * @return void
     * @throws TokenInvalidException
     * @throws OAuthCallbackException
     */
    private function handleGuzzleException(
        GuzzleException $exceptionThrown,
        string $exceptionToThrow,
        array $data
    ): void {
        if (method_exists($exceptionThrown, 'getResponse')) {
            $response = $exceptionThrown->getResponse();
            if ($response) {
                $data['response_data'] = [
                    'http_status'      => $response->getStatusCode(),
                    'http_reason'      => $response->getReasonPhrase(),
                    'response_body'    => (string) $response->getBody(),
                    'response_headers' => $response->getHeaders(),
                ];
            }
        }

        $this->handleGeneralException($exceptionThrown, $exceptionToThrow, $data);
    }

    /**
     * Handles general exceptions by throwing specific exceptions based on the context provided in the data.
     *
     * @param Throwable $exceptionThrown The exception that occurred.
     * @param string $exceptionToThrow The exception to throw.
     * @param array $data An associative array containing information about the exception context.
     * @return void
     * @throws TokenInvalidException
     * @throws OAuthCallbackException
     */
    private function handleGeneralException(
        Throwable $exceptionThrown,
        string $exceptionToThrow,
        array $data
    ): void {
        $message = "Error during {$data['context']}: {$exceptionThrown->getMessage()}";
        $code = $exceptionThrown->getCode();

        throw new $exceptionToThrow($message, $code, $data, $exceptionThrown);
    }


    /**
     * Check if the client is authenticated
     *
     * @return bool
     */
    public function hasToken(): bool
    {
        return $this->accessToken !== null;
    }

    /**
     * Check if access token is valid (PATs are always valid unless null).
     *
     * @return bool True if token is valid (either a valid OAuth token or PAT)
     * @throws TokenInvalidException If no token or it's expired and error refreshing it.
     */
    public function ensureValidToken(): bool
    {
        if (!$this->hasToken()) {
            throw new TokenInvalidException('No access token is available.');
        }

        // If token has no expiration (e.g., PAT), it is considered valid
        if (!$this->accessToken->getExpires()) {
            return true;
        }

        // Handle OAuth tokens that may need refreshing
        if ($this->accessToken->hasExpired()) {
            try {
                $this->accessToken = $this->authHandler->refreshToken($this->accessToken);
                $this->notifyTokenRefreshSubscribers($this->accessToken);
                return true;
            } catch (GuzzleException $e) {
                $this->handleGuzzleException($e, TokenInvalidException::class, ['context' => 'Refresh token']);
            } catch (Exception $e) {
                $this->handleGeneralException($e, TokenInvalidException::class, ['context' => 'Refresh token']);
            }
        }

        return true;
    }

    /**
     * Retrieves the current access token.
     *
     * @return array|null The current access token, or null if not set.
     */
    public function getAccessToken(): ?array
    {
        return $this->accessToken->jsonSerialize();
    }

    /**
     * Refreshes the expired access token.
     *
     * @return array|null Returns the current access token, after refreshing it if necessary.
     *
     * @throws TokenInvalidException If no token or it's expired and error refreshing it.
     */
    public function refreshToken(): ?array
    {
        if (!$this->hasToken()) {
            throw new TokenInvalidException('No access token is available.');
        }

        try {
            $this->accessToken = $this->authHandler->refreshToken($this->accessToken);
        } catch (GuzzleException $e) {
            $this->handleGuzzleException($e, TokenInvalidException::class, ['context' => 'Refresh token']);
        } catch (IdentityProviderException $e) {
            $this->handleGeneralException($e, TokenInvalidException::class, ['context' => 'Refresh token']);
        }

        $this->notifyTokenRefreshSubscribers($this->accessToken);
        return $this->accessToken->jsonSerialize();
    }
    /**
     * Register a callback to be invoked when the access token is refreshed.
     * Can also be used to modify existing callbacks by passing in the key of the callback to modify.
     *
     * @param callable $callback The callback function to register.
     *                           It should accept one parameter: the refreshed access token.
     * @param string|int|null $key ID for the callback. If absent, next numeric index will be used.
     *
     * @return string|int The key of the registered callback.
     */
    public function onTokenRefresh(callable $callback, $key = null)
    {
        if (is_null($key)) {
            // Use the next numeric index if no key is provided
            $this->tokenRefreshSubscribers[] = $callback;
            return array_key_last($this->tokenRefreshSubscribers);
        }

        // Use the provided key
        $this->tokenRefreshSubscribers[$key] = $callback;
        return $key;
    }

    /**
     * Unregister a callback from the token refresh event using its index.
     *
     * @param string|int $key The index of the callback to unregister.
     * @return bool True if the callback was removed, false if the index was invalid.
     */
    public function removeTokenRefreshSubscriber($key): bool
    {
        if (isset($this->tokenRefreshSubscribers[$key])) {
            unset($this->tokenRefreshSubscribers[$key]);
            return true;
        }
        return false;
    }


    /**
     * Notify all registered subscribers about the refreshed token.
     *
     * @param AccessToken $token The refreshed access token.
     */
    private function notifyTokenRefreshSubscribers(AccessToken $token): void
    {
        foreach ($this->tokenRefreshSubscribers as $callback) {
            $callback($token->jsonSerialize());
        }
    }

    /**
     * Get API client with valid token
     *
     * @return AsanaApiClient
     * @throws TokenInvalidException If not authenticated
     */
    private function getApiClient(): AsanaApiClient
    {
        $this->ensureValidToken();

        if ($this->apiClient === null) {
            $this->apiClient = new AsanaApiClient($this->accessToken->getToken());
        }

        return $this->apiClient;
    }

    /**
     * Loads and decrypts the token stored in the specified path, initializing it for further use.
     * If the token file does not exist or an error occurs during the loading process, the method fails gracefully.
     *
     * @param string $salt A unique string used to derive the decryption and MAC keys.
     *                       For generating a secure salt, consider using tools like:
     *                        - https://www.symbionts.de/tools/random-password-salt-generator.html
     *                        - https://www.vondy.com/random-salt-generator--Pn9HVxnU
     *                      For more information about salts, see, for example, one of the following:
     *                        - https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html#salting
     *                        - https://crypto.stackexchange.com/questions/44976/how-do-i-create-an-effective-salt
     *                        - https://stackoverflow.com/questions/2513734/generating-a-salt-in-php/34173258#34173258
     *
     *
     * @return bool True if the token was successfully loaded and decrypted, false otherwise.
     */
    public function loadToken(string $salt): bool
    {
        if (file_exists($this->tokenStoragePath)) {
            try {
                // Read and decode the stored token data
                $tokenData = json_decode(file_get_contents($this->tokenStoragePath), true, 512, JSON_THROW_ON_ERROR);

                // Decrypt sensitive fields
                $tokenData['access_token'] = CryptoUtils::decrypt($tokenData['access_token'], $salt);
                if (isset($tokenData['refresh_token'])) {
                    $tokenData['refresh_token'] = CryptoUtils::decrypt($tokenData['refresh_token'], $salt);
                }

                // Set the access token in the library
                $this->accessToken = new AccessToken($tokenData);
                return true;
            } catch (Exception $e) {
                $this->accessToken = null;
                return false;
            }
        }
        return false;
    }

    /**
     * Retrieves and decrypts a token from the specified storage path.
     * If no storage path is provided, it defaults to a file named 'token.json' in the current working directory.
     *
     * @param string $salt A unique string used to derive the decryption and MAC keys.
     *                       For generating a secure salt, consider using tools like:
     *                        - https://www.symbionts.de/tools/random-password-salt-generator.html
     *                        - https://www.vondy.com/random-salt-generator--Pn9HVxnU
     *                      For more information about salts, see, for example, one of the following:
     *                        - https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html#salting
     *                        - https://crypto.stackexchange.com/questions/44976/how-do-i-create-an-effective-salt
     *                        - https://stackoverflow.com/questions/2513734/generating-a-salt-in-php/34173258#34173258
     * @param string|null $tokenStoragePath The path to the file where the token is stored. Optional.
     *
     * @return array The decrypted token data, including 'access_token' and optionally 'refresh_token'.
     * @throws JsonException If there is an error decoding the JSON from the token storage file.
     * @throws Exception If the OpenSSL extension is unavailable, MAC verification fails, or decryption fails.
     */
    public static function retrieveToken(string $salt, ?string $tokenStoragePath = null): array
    {
        if (is_null($tokenStoragePath)) {
            $tokenStoragePath = getcwd() . '/token.json';
        }
        $token = json_decode(file_get_contents($tokenStoragePath), true, 512, JSON_THROW_ON_ERROR);
        $token['access_token'] = CryptoUtils::decrypt($token['access_token'], $salt);
        if (isset($token['refresh_token'])) {
            $token['refresh_token'] = CryptoUtils::decrypt($token['refresh_token'], $salt);
        }
        return $token;
    }

    /**
     * Encrypts the current access token using the provided salt and saves it to the defined storage path.
     * If no access token is available, the method does nothing.
     *
     * @param string $salt A unique string used to derive the decryption and MAC keys.
     *                       For generating a secure salt, consider using tools like:
     *                        - https://www.symbionts.de/tools/random-password-salt-generator.html
     *                        - https://www.vondy.com/random-salt-generator--Pn9HVxnU
     *                      For more information about salts, see, for example, one of the following:
     *                        - https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html#salting
     *                        - https://crypto.stackexchange.com/questions/44976/how-do-i-create-an-effective-salt
     *                        - https://stackoverflow.com/questions/2513734/generating-a-salt-in-php/34173258#34173258
     *
     * @return void
     * @throws Exception If the OpenSSL extension is unavailable or encryption fails.
     */
    public function saveToken(string $salt): void
    {
        if ($this->accessToken) {
            $token = $this->accessToken->jsonSerialize();

            // Encrypt sensitive fields
            $token['access_token'] = CryptoUtils::encrypt($token['access_token'], $salt);
            if (isset($token['refresh_token'])) {
                $token['refresh_token'] = CryptoUtils::encrypt($token['refresh_token'], $salt);
            }

            // Save the resulting token to the storage path
            file_put_contents($this->tokenStoragePath, json_encode($token));
        }
    }

    /**
     * Clear stored token (logout)
     */
    public function logout(): void
    {
        $this->accessToken = null;
        $this->apiClient = null;

        if (file_exists($this->tokenStoragePath)) {
            unlink($this->tokenStoragePath);
        }
    }
}
