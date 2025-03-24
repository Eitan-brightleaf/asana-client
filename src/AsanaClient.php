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
     * Initialize the Asana client
     *
     * @param string $clientId OAuth client ID
     * @param string $clientSecret OAuth client secret
     * @param string $redirectUri OAuth redirect URI
     * @param string|null $tokenStoragePath Path to store the token file
     */
    public function __construct(
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        string $tokenStoragePath = null
    ) {
        $this->authHandler = new AsanaOAuthHandler($clientId, $clientSecret, $redirectUri);
        $this->tokenStoragePath = $tokenStoragePath ?? __DIR__ . '/token.json';
        
        // Try to load existing token
        $this->loadToken();
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
     * @return bool True if authentication was successful
     */
    public function handleCallback(string $authorizationCode): bool
    {
        try {
            $this->accessToken = $this->authHandler->getAccessToken($authorizationCode);
            $this->saveToken();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Check if the client is authenticated
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->accessToken !== null;
    }
    
    /**
     * Check if access token needs refresh and handle it
     *
     * @return bool True if token is valid (either didn't need refresh or was refreshed successfully)
     */
    public function ensureValidToken(): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        if ($this->accessToken->hasExpired()) {
            try {
                $this->accessToken = $this->authHandler->refreshToken($this->accessToken);
                $this->saveToken();
                return true;
            } catch (Exception $e) {
                $this->accessToken = null;
                return false;
            }
        }
        
        return true;
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
    private function loadToken(): void
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
    private function saveToken(): void
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