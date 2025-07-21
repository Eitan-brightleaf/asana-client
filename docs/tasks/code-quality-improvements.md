# Code Quality Improvements

This document outlines code quality enhancements needed for the Asana Client PHP library. Each item includes detailed explanations, code examples, and validation against API specifications.

## 1. Implement static analysis tools

### Problem Statement
The codebase currently lacks static analysis tools that can identify potential bugs, code smells, and other issues before they cause problems in production. Static analysis tools like PHPStan or Psalm can catch many common issues early in the development process.

### Code Examples

#### Current Implementation:
```php
// No static analysis configuration exists
// Example of code that would trigger static analysis warnings:

// In src/Api/TaskApiService.php
public function getTask($taskId, $options = [])
{
    // Missing parameter validation
    return $this->client->request('GET', "tasks/{$taskId}", ['query' => $options]);
}

// In src/Http/ApiClient.php
public function request($method, $endpoint, $params = [])
{
    // Undefined variable usage
    if ($invalidParam) {
        throw new \Exception('Invalid parameter');
    }
    
    // No return type declarations
    try {
        $response = $this->httpClient->request($method, $endpoint, $params);
        return json_decode($response->getBody(), true);
    } catch (RequestException $e) {
        throw new AsanaApiException($e->getMessage(), $e->getCode(), $e);
    }
}
```

#### Expected Implementation:
```php
// In phpstan.neon
parameters:
  level: 7
  paths:
    - src
  excludePaths:
    - vendor
  checkMissingIterableValueType: false
  checkGenericClassInNonGenericObjectType: false

// In src/Api/TaskApiService.php
/**
 * Get a specific task by ID.
 *
 * @param string $taskId The task ID to get
 * @param array<string, mixed> $options Request options
 * @return array<string, mixed> The API response
 * @throws AsanaApiException If the API request fails
 */
public function getTask(string $taskId, array $options = []): array
{
    if (empty($taskId)) {
        throw new \InvalidArgumentException('Task ID cannot be empty');
    }
    
    return $this->client->request('GET', "tasks/{$taskId}", ['query' => $options]);
}

// In src/Http/ApiClient.php
/**
 * Make a request to the API.
 *
 * @param string $method HTTP method (GET, POST, PUT, DELETE)
 * @param string $endpoint API endpoint
 * @param array<string, mixed> $params Request parameters
 * @return array<string, mixed> The API response
 * @throws AsanaApiException If the API request fails
 */
public function request(string $method, string $endpoint, array $params = []): array
{
    try {
        $response = $this->httpClient->request($method, $endpoint, $params);
        $result = json_decode($response->getBody(), true);
        
        if (!is_array($result)) {
            throw new AsanaApiException('Invalid API response format', 500);
        }
        
        return $result;
    } catch (RequestException $e) {
        throw new AsanaApiException($e->getMessage(), $e->getCode(), $e);
    }
}
```

### File References
- `phpstan.neon`: New configuration file for PHPStan
- `psalm.xml`: Alternative configuration file for Psalm
- `composer.json`: Needs updates to include static analysis tools
- All PHP files in the `src/` directory

### API Spec Validation
Static analysis doesn't directly relate to API specification compliance, but it helps ensure that the code correctly implements the API specification by catching type errors, undefined variables, and other issues that could lead to incorrect API usage.

### Critical Evaluation
- **Actual Impact**: High - Without static analysis, the codebase may contain hidden bugs and issues
- **Priority Level**: High - Should be addressed early to improve code quality
- **Implementation Status**: Not implemented - No static analysis tools are currently used
- **Spec Compliance**: N/A - This is a development process concern
- **Difficulty/Complexity**: Medium - Requires configuring static analysis tools and fixing identified issues, but follows established patterns

### Recommended Action
Implement PHPStan or Psalm for static analysis, configure it with an appropriate strictness level, and fix all identified issues. Add static analysis to the CI pipeline to prevent new issues from being introduced.

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