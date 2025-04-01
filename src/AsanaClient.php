<?php

namespace BrightleafDigital;

use BrightleafDigital\Api\AttachmentApiService;
use BrightleafDigital\Api\MembershipApiService;
use BrightleafDigital\Api\ProjectApiService;
use BrightleafDigital\Api\SectionApiService;
use BrightleafDigital\Api\TagsApiService;
use BrightleafDigital\Api\TaskApiService;
use BrightleafDigital\Api\UserApiService;
use BrightleafDigital\Auth\AsanaOAuthHandler;
use BrightleafDigital\Http\AsanaApiClient;
use Exception;
use League\OAuth2\Client\Token\AccessToken;

class AsanaClient
{
    /**
     * OAuth handler for authentication with Asana API
     * @var AsanaOAuthHandler
     */
    private AsanaOAuthHandler $authHandler;

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
     * Constructor method for initializing the AsanaOAuthHandler and setting the token storage path.
     *
     * @param string|null $clientId Optional client ID for authentication.
     * @param string|null $clientSecret Optional client secret for authentication.
     * @param string|null $redirectUri Optional redirect URI for OAuth flow.
     * @param string|null $tokenStoragePath Path to token storage file, defaults to a token.json file in the current directory.
     *
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
	public static function withPAT(
		string $personalAccessToken
	): self {
		$instance = new self('', '', '', ''); // Empty clientId, clientSecret, and redirectUri not needed for PAT
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
     * Get the authorization URL for OAuth flow
     *
     * @return string
     */
    public function getAuthorizationUrl(): string
    {
        return $this->authHandler->getAuthorizationUrl();
    }

    /**
     * Get authorization URL, state, and PKCE verifier
     *
     * @param bool $enableState
     * @param bool $enablePKCE
     * @return array ['url' => string, 'state' => string|null, 'codeVerifier' => string|null]
     */
    public function getSecureAuthorizationUrl(bool $enableState = true, bool $enablePKCE = true): array
    {
        return $this->authHandler->getSecureAuthorizationUrl($enableState, $enablePKCE);
    }


    /**
     * Handle callback and retrieve an access token
     *
     * @param string $authorizationCode
     * @param string|null $codeVerifier
     * @return array|null Access Token data as array or null on failure
     */
    public function handleCallback(string $authorizationCode, ?string $codeVerifier = null): ?array
    {
        try {
            $this->accessToken = $this->authHandler->handleCallback($authorizationCode, $codeVerifier);
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

    /**
     * Refreshes the expired access token.
     *
     * @return AccessToken|null Returns the refreshed access token if it was expired, otherwise null.
     */
    public function refreshToken(): ?AccessToken
	{
		if ($this->accessToken && $this->accessToken->hasExpired()) {
			$this->accessToken = $this->authHandler->refreshToken($this->accessToken);
			return $this->accessToken;
		}
		return null;
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