# Code Quality Improvements

This document outlines code quality enhancements needed for the Asana Client PHP library. Each item includes detailed explanations, code examples, and validation against API specifications.

## 2. Implement proper error logging

### Problem Statement
The current error handling lacks proper logging, making it difficult to diagnose issues in production. Implementing structured logging would improve error visibility and help with troubleshooting.

### Code Examples

#### Current Implementation:
```php
// In Http/ApiClient.php
public function request($method, $endpoint, $params = [])
{
    try {
        $response = $this->httpClient->request($method, $endpoint, $params);
        return json_decode($response->getBody(), true);
    } catch (RequestException $e) {
        // No logging, just throws the exception
        throw new AsanaApiException($e->getMessage(), $e->getCode(), $e);
    }
}

// In Auth/OAuthHandler.php
public function handleCallback($code)
{
    try {
        $token = $this->provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);
        return $token;
    } catch (\Exception $e) {
        // No logging, just throws the exception
        throw new OAuthCallbackException('OAuth callback failed: ' . $e->getMessage());
    }
}
```

#### Expected Implementation:
```php
// In Http/ApiClient.php
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ApiClient
{
    private $httpClient;
    private $logger;
    
    public function __construct($baseUrl, $timeout = 30, LoggerInterface $logger = null)
    {
        $this->httpClient = new Client([
            'base_uri' => $baseUrl,
            'timeout' => $timeout
        ]);
        
        $this->logger = $logger ?? new NullLogger();
    }
    
    public function request($method, $endpoint, $params = [])
    {
        $this->logger->debug('Making API request', [
            'method' => $method,
            'endpoint' => $endpoint,
            'params' => $this->sanitizeParams($params)
        ]);
        
        try {
            $response = $this->httpClient->request($method, $endpoint, $params);
            $result = json_decode($response->getBody(), true);
            
            $this->logger->debug('API request successful', [
                'method' => $method,
                'endpoint' => $endpoint,
                'status_code' => $response->getStatusCode()
            ]);
            
            return $result;
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 0;
            $responseBody = $response ? (string) $response->getBody() : '';
            
            $this->logger->error('API request failed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'status_code' => $statusCode,
                'error' => $e->getMessage(),
                'response' => $this->truncateResponseBody($responseBody)
            ]);
            
            throw new AsanaApiException($e->getMessage(), $statusCode, $e);
        }
    }
    
    private function sanitizeParams($params)
    {
        // Remove sensitive information like auth tokens
        $sanitized = $params;
        
        if (isset($sanitized['headers']['Authorization'])) {
            $sanitized['headers']['Authorization'] = 'REDACTED';
        }
        
        return $sanitized;
    }
    
    private function truncateResponseBody($body, $maxLength = 1000)
    {
        if (strlen($body) <= $maxLength) {
            return $body;
        }
        
        return substr($body, 0, $maxLength) . '... [truncated]';
    }
}

// In Auth/OAuthHandler.php
class OAuthHandler
{
    private $provider;
    private $logger;
    
    public function __construct($clientId, $clientSecret, $redirectUri, LoggerInterface $logger = null)
    {
        $this->provider = new GenericProvider([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => $redirectUri,
            'urlAuthorize' => 'https://app.asana.com/-/oauth_authorize',
            'urlAccessToken' => 'https://app.asana.com/-/oauth_token',
            'urlResourceOwnerDetails' => 'https://app.asana.com/api/1.0/users/me'
        ]);
        
        $this->logger = $logger ?? new NullLogger();
    }
    
    public function handleCallback($code, $state)
    {
        $this->logger->info('Processing OAuth callback', [
            'state' => $state
        ]);
        
        try {
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);
            
            $this->logger->info('OAuth token obtained successfully', [
                'expires' => $token->getExpires()
            ]);
            
            return $token;
        } catch (\Exception $e) {
            $this->logger->error('OAuth callback failed', [
                'error' => $e->getMessage()
            ]);
            
            throw new OAuthCallbackException('OAuth callback failed: ' . $e->getMessage(), 0, $e);
        }
    }
}

// In AsanaClient.php
class AsanaClient
{
    private $apiClient;
    private $logger;
    
    public function __construct($clientId, $clientSecret, $redirectUri, $config = [], LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
        
        $baseUrl = $config['base_url'] ?? 'https://app.asana.com/api/1.0';
        $timeout = $config['timeout'] ?? 30;
        
        $this->apiClient = new ApiClient($baseUrl, $timeout, $this->logger);
        $this->oauthHandler = new OAuthHandler($clientId, $clientSecret, $redirectUri, $this->logger);
        
        $this->logger->info('AsanaClient initialized', [
            'base_url' => $baseUrl
        ]);
    }
    
    // Other methods...
}
```

### File References
- `src/Http/ApiClient.php`: API client that needs logging
- `src/Auth/OAuthHandler.php`: OAuth handler that needs logging
- `src/AsanaClient.php`: Main client class that needs logging
- `composer.json`: Needs updates to include PSR-3 logger interface

### API Spec Validation
Proper error logging doesn't directly relate to API specification compliance, but it helps diagnose issues with API interactions and ensure that the client is correctly implementing the API specification.

### Critical Evaluation
- **Actual Impact**: High - Without proper logging, it's difficult to diagnose issues in production
- **Priority Level**: High - Should be addressed to improve reliability and maintainability
- **Implementation Status**: Not implemented - Current code lacks structured logging
- **Spec Compliance**: N/A - This is a development process concern
- **Difficulty/Complexity**: Medium - Requires implementing PSR-3 logging throughout the codebase and ensuring sensitive data is properly sanitized

### Recommended Action
Implement structured logging using a PSR-3 compatible logger, add appropriate log messages at different levels (debug, info, warning, error), and ensure sensitive information is not logged. Make the logger injectable to allow applications to use their preferred logging implementation.