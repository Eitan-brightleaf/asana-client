<?php

namespace BrightleafDigital;

use BrightleafDigital\Api\ProjectApiService;
use BrightleafDigital\Api\TaskApiService;
use BrightleafDigital\Auth\AsanaOAuthHandler;
use BrightleafDigital\Http\AsanaApiClient;
use Exception;
use League\OAuth2\Client\Token\AccessToken;

class AsanaClient
{
    private AsanaOAuthHandler $authHandler;
    private ?AsanaApiClient $apiClient = null;
    private ?AccessToken $accessToken = null;
    private string $tokenStoragePath;

	/**
	 * Initialize Asana client
	 *
	 * @param string|null $clientId OAuth client ID (not needed for PAT)
	 * @param string|null $clientSecret OAuth client secret (not needed for PAT)
	 * @param string|null $redirectUri OAuth redirect URI (not needed for PAT)
	 * @param string|null $tokenStoragePath Path to store the token file (optional)
	 */
	public function __construct(
		?string $clientId = null,
		?string $clientSecret = null,
		?string $redirectUri = null,
		string $tokenStoragePath = null
	) {
		if ($clientId && $clientSecret) {
			$this->authHandler = new AsanaOAuthHandler($clientId, $clientSecret, $redirectUri);
		}

		$this->tokenStoragePath = $tokenStoragePath ?? __DIR__ . '/token.json';
	}

	/**
	 * Initialize the Asana client with an access token
	 *
	 * @param string $clientId OAuth client ID
	 * @param string $clientSecret OAuth client secret
	 * @param array $token The user's preexisting access token
	 * @return self
	 */
	public static function withAccessToken(
		string $clientId,
		string $clientSecret,
		array $token
	): self {
		$instance = new self(
			$clientId,
			$clientSecret,
			'' // No redirect URI required when preloading a token
		);
		$instance->accessToken = new AccessToken($token);
		return $instance;
	}

	/**
	 * Initialize the Asana client with a Personal Access Token (PAT)
	 *
	 * @param string $personalAccessToken The user's PAT from Asana
	 * @return self
	 */
	public static function withPersonalAccessToken(
		string $personalAccessToken
	): self {
		$instance = new self('', '', '', ''); // Empty clientId, clientSecret, and redirectUri not needed for PAT
		$instance->accessToken = new AccessToken(['access_token' => $personalAccessToken]);
		return $instance;
	}

    /**
     * Get the authorization URL for OAuth flow
     *
     * @return string
     */
    public function getAuthorizationUrl(): string
    {
        return $this->authHandler->getAuthorizationUrl();
    }
    
    /**
     * Handle authorization callback and get access token
     *
     * @param string $authorizationCode The code from callback
     *
     * @return array True if authentication was successful
     */
    public function handleCallback(string $authorizationCode ): ?array
    {
        try {
            $this->accessToken = $this->authHandler->getAccessToken($authorizationCode);
	        // Return the token's data as an associative array
	        return $this->accessToken->jsonSerialize();
        } catch (Exception $e) {
            return null;
        }
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
	 */
	public function ensureValidToken(): bool
	{
		if (!$this->hasToken()) {
			return false;
		}

		// If token has no expiration (e.g., PAT), it is considered valid
		if (!$this->accessToken->getExpires()) {
			return true;
		}

		// Handle OAuth tokens that may need refreshing
		if ($this->accessToken->hasExpired()) {
			try {
				$this->accessToken = $this->authHandler->refreshToken($this->accessToken);
				return true;
			} catch (Exception $e) {
				$this->accessToken = null;
				return false;
			}
		}

		return true;
	}

	public function refreshToken(): ?AccessToken
	{
		if ($this->accessToken && $this->accessToken->hasExpired()) {
			$this->accessToken = $this->authHandler->refreshToken($this->accessToken);
			return $this->accessToken;
		}
		return null;
	}
    
    /**
     * Get task API service
     *
     * @return TaskApiService
     * @throws Exception If not authenticated
     */
    public function tasks(): TaskApiService
    {
        return new TaskApiService($this->getApiClient());
    }
    
    /**
     * Get project API service
     *
     * @return ProjectApiService
     * @throws Exception If not authenticated
     */
    public function projects(): ProjectApiService
    {
        return new ProjectApiService($this->getApiClient());
    }
    
    /**
     * Get API client with valid token
     *
     * @return AsanaApiClient
     * @throws Exception If not authenticated
     */
    private function getApiClient(): AsanaApiClient
    {
        if (!$this->ensureValidToken()) {
            throw new Exception('Not authenticated or token expired');
        }
        
        if ($this->apiClient === null) {
            $this->apiClient = new AsanaApiClient($this->accessToken->getToken());
        }
        
        return $this->apiClient;
    }
    
    /**
     * Load token from storage
     */
    public function loadToken(): void
    {
        if (file_exists($this->tokenStoragePath)) {
            try {
                $tokenData = json_decode(file_get_contents($this->tokenStoragePath), true);
                $this->accessToken = new AccessToken($tokenData);
            } catch (Exception $e) {
                $this->accessToken = null;
            }
        }
    }
    
    /**
     * Save token to storage
     */
    public function saveToken(): void
    {
        if ($this->accessToken) {
            file_put_contents(
                $this->tokenStoragePath,
                json_encode($this->accessToken->jsonSerialize())
            );
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