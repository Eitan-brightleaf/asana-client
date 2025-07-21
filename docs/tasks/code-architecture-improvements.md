# Code Architecture Improvements

This document outlines architectural enhancements needed for the Asana Client PHP library. Each item includes detailed explanations, code examples, and validation against API specifications.

## 1. Refactor API service classes to reduce duplication

### Problem Statement
The current API service classes contain significant code duplication, particularly in request handling and response processing. This makes maintenance difficult and increases the likelihood of inconsistencies.

### Code Examples

#### Current Implementation:
```php
// In Api/TaskApiService.php
public function getTasks($options = [])
{
    return $this->client->request('GET', 'tasks', ['query' => $options]);
}

public function getTask($taskId, $options = [])
{
    return $this->client->request('GET', "tasks/{$taskId}", ['query' => $options]);
}

// In Api/ProjectApiService.php
public function getProjects($options = [])
{
    return $this->client->request('GET', 'projects', ['query' => $options]);
}

public function getProject($projectId, $options = [])
{
    return $this->client->request('GET', "projects/{$projectId}", ['query' => $options]);
}
```

#### Expected Implementation:
```php
// In Api/BaseApiService.php
abstract class BaseApiService
{
    protected $client;
    protected $resourceName;
    
    public function __construct($client)
    {
        $this->client = $client;
    }
    
    protected function getAll($options = [])
    {
        return $this->client->request('GET', $this->resourceName, ['query' => $options]);
    }
    
    protected function getById($id, $options = [])
    {
        return $this->client->request('GET', "{$this->resourceName}/{$id}", ['query' => $options]);
    }
    
    protected function create($data)
    {
        return $this->client->request('POST', $this->resourceName, ['json' => ['data' => $data]]);
    }
    
    protected function update($id, $data)
    {
        return $this->client->request('PUT', "{$this->resourceName}/{$id}", ['json' => ['data' => $data]]);
    }
    
    protected function delete($id)
    {
        return $this->client->request('DELETE', "{$this->resourceName}/{$id}");
    }
}

// In Api/TaskApiService.php
class TaskApiService extends BaseApiService
{
    protected $resourceName = 'tasks';
    
    public function getTasks($options = [])
    {
        return $this->getAll($options);
    }
    
    public function getTask($taskId, $options = [])
    {
        return $this->getById($taskId, $options);
    }
    
    // Task-specific methods here
}

// In Api/ProjectApiService.php
class ProjectApiService extends BaseApiService
{
    protected $resourceName = 'projects';
    
    public function getProjects($options = [])
    {
        return $this->getAll($options);
    }
    
    public function getProject($projectId, $options = [])
    {
        return $this->getById($projectId, $options);
    }
    
    // Project-specific methods here
}
```

### File References
- `src/Api/TaskApiService.php`: Contains task-related API methods
- `src/Api/ProjectApiService.php`: Contains project-related API methods
- `src/Api/WorkspaceApiService.php`: Contains workspace-related API methods
- `src/Api/UserApiService.php`: Contains user-related API methods

### API Spec Validation
The Asana API follows RESTful principles with consistent patterns for resource operations. A base service class would align well with this structure while maintaining compliance with the API specification.

### Critical Evaluation
- **Actual Impact**: Medium - Code duplication increases maintenance burden and risk of inconsistencies
- **Priority Level**: High - Should be addressed early in the refactoring process
- **Implementation Status**: Not implemented - Current code has significant duplication
- **Spec Compliance**: N/A - This is a client-side architecture concern, not an API specification issue
- **Difficulty/Complexity**: Medium - Requires refactoring existing API service classes and creating base abstractions, but follows established inheritance patterns

### Recommended Action
Create a BaseApiService class that implements common CRUD operations and have specific API service classes extend it. This will reduce duplication and ensure consistent behavior across all API services.

## 2. Implement interfaces for all major components

### Problem Statement
The current codebase lacks interfaces for major components, making it difficult to implement alternative implementations or mock components for testing.

### Code Examples

#### Current Implementation:
```php
// Direct class usage without interfaces
class AsanaClient
{
    private $httpClient;
    private $oauthHandler;
    
    public function __construct($clientId, $clientSecret, $redirectUri)
    {
        $this->httpClient = new ApiClient();
        $this->oauthHandler = new OAuthHandler($clientId, $clientSecret, $redirectUri);
    }
    
    // Methods that directly use concrete implementations
}

// Usage in application code
$asanaClient = new AsanaClient($clientId, $clientSecret, $redirectUri);
```

#### Expected Implementation:
```php
// Define interfaces
interface HttpClientInterface
{
    public function request($method, $endpoint, $params = []);
}

interface AuthHandlerInterface
{
    public function getAuthorizationUrl($options = []);
    public function handleCallback($code, $state);
    public function refreshToken($refreshToken);
}

interface AsanaClientInterface
{
    public function tasks();
    public function projects();
    public function users();
    // Other resource methods
}

// Implement interfaces
class ApiClient implements HttpClientInterface
{
    public function request($method, $endpoint, $params = [])
    {
        // Implementation
    }
}

class OAuthHandler implements AuthHandlerInterface
{
    // Implementation of interface methods
}

class AsanaClient implements AsanaClientInterface
{
    private $httpClient;
    private $authHandler;
    
    public function __construct(HttpClientInterface $httpClient, AuthHandlerInterface $authHandler)
    {
        $this->httpClient = $httpClient;
        $this->authHandler = $authHandler;
    }
    
    // Implementation of interface methods
}

// Usage in application code with dependency injection
$httpClient = new ApiClient();
$authHandler = new OAuthHandler($clientId, $clientSecret, $redirectUri);
$asanaClient = new AsanaClient($httpClient, $authHandler);
```

### File References
- `src/Http/ApiClient.php`: HTTP client implementation
- `src/Auth/OAuthHandler.php`: OAuth authentication handler
- `src/AsanaClient.php`: Main client class

### API Spec Validation
This is a client-side architecture concern and doesn't directly relate to API specification compliance. However, a well-designed interface structure makes it easier to adapt to API changes or extensions.

### Critical Evaluation
- **Actual Impact**: Medium - Lack of interfaces makes the code less flexible and harder to test
- **Priority Level**: High - Should be addressed early to enable other improvements
- **Implementation Status**: Not implemented - Current code uses concrete classes without interfaces
- **Spec Compliance**: N/A - This is a client-side architecture concern
- **Difficulty/Complexity**: High - Requires designing comprehensive interfaces, refactoring existing classes to implement them, and updating dependency injection throughout the codebase

### Recommended Action
Define interfaces for all major components (HTTP client, authentication handlers, API services) and update implementations to use these interfaces. This will improve testability and flexibility.

## 3. Separate configuration from implementation

### Problem Statement
Configuration options are currently hardcoded or tightly coupled with implementation classes, making it difficult to customize behavior without modifying code.

### Code Examples

#### Current Implementation:
```php
// Configuration mixed with implementation
class AsanaClient
{
    private $baseUrl = 'https://app.asana.com/api/1.0';
    private $timeout = 30;
    
    public function __construct($clientId, $clientSecret, $redirectUri)
    {
        $this->httpClient = new ApiClient($this->baseUrl, $this->timeout);
        // Other initialization
    }
    
    // Methods with hardcoded configuration values
}
```

#### Expected Implementation:
```php
// Separate configuration class
class AsanaClientConfig
{
    private $baseUrl;
    private $timeout;
    private $retryAttempts;
    private $userAgent;
    
    public function __construct(array $options = [])
    {
        $this->baseUrl = $options['base_url'] ?? 'https://app.asana.com/api/1.0';
        $this->timeout = $options['timeout'] ?? 30;
        $this->retryAttempts = $options['retry_attempts'] ?? 3;
        $this->userAgent = $options['user_agent'] ?? 'AsanaClient PHP/' . PHP_VERSION;
    }
    
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }
    
    public function getTimeout()
    {
        return $this->timeout;
    }
    
    public function getRetryAttempts()
    {
        return $this->retryAttempts;
    }
    
    public function getUserAgent()
    {
        return $this->userAgent;
    }
}

// Implementation using configuration
class AsanaClient
{
    private $config;
    private $httpClient;
    
    public function __construct(AsanaClientConfig $config, HttpClientInterface $httpClient = null)
    {
        $this->config = $config;
        $this->httpClient = $httpClient ?? new ApiClient(
            $this->config->getBaseUrl(),
            $this->config->getTimeout(),
            $this->config->getUserAgent()
        );
    }
    
    // Methods using configuration from config object
}

// Usage
$config = new AsanaClientConfig([
    'timeout' => 60,
    'retry_attempts' => 5
]);
$asanaClient = new AsanaClient($config);
```

### File References
- `src/AsanaClient.php`: Main client class with configuration values
- `src/Http/ApiClient.php`: HTTP client with configuration values

### API Spec Validation
This is a client-side architecture concern and doesn't directly relate to API specification compliance. However, a flexible configuration system makes it easier to adapt to different API usage patterns.

### Critical Evaluation
- **Actual Impact**: Medium - Hardcoded configuration makes the library less flexible
- **Priority Level**: Medium - Should be addressed to improve customization options
- **Implementation Status**: Not implemented - Current code has configuration mixed with implementation
- **Spec Compliance**: N/A - This is a client-side architecture concern
- **Difficulty/Complexity**: Medium - Requires creating configuration classes and refactoring existing code to use configurable values, but follows established patterns

### Recommended Action
Create a dedicated configuration class that encapsulates all configurable options. Update all components to use this configuration class instead of hardcoded values.

## 4. Implement proper service container/dependency injection

### Problem Statement
The current code manually instantiates dependencies, making it difficult to replace components or inject mock objects for testing.

### Code Examples

#### Current Implementation:
```php
// Manual dependency instantiation
class AsanaClient
{
    private $taskService;
    private $projectService;
    
    public function __construct($clientId, $clientSecret, $redirectUri)
    {
        $httpClient = new ApiClient();
        $this->taskService = new TaskApiService($httpClient);
        $this->projectService = new ProjectApiService($httpClient);
        // Other services
    }
    
    public function tasks()
    {
        return $this->taskService;
    }
    
    public function projects()
    {
        return $this->projectService;
    }
}
```

#### Expected Implementation:
```php
// Using dependency injection
class AsanaClient
{
    private $services = [];
    private $httpClient;
    
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    
    public function tasks()
    {
        return $this->getService('tasks', function() {
            return new TaskApiService($this->httpClient);
        });
    }
    
    public function projects()
    {
        return $this->getService('projects', function() {
            return new ProjectApiService($this->httpClient);
        });
    }
    
    private function getService($name, callable $factory)
    {
        if (!isset($this->services[$name])) {
            $this->services[$name] = $factory();
        }
        
        return $this->services[$name];
    }
    
    // Method to register custom service implementations
    public function registerService($name, $service)
    {
        $this->services[$name] = $service;
        return $this;
    }
}

// Usage with custom service
$httpClient = new ApiClient();
$asanaClient = new AsanaClient($httpClient);

// Replace a service with custom implementation
$customTaskService = new CustomTaskApiService($httpClient);
$asanaClient->registerService('tasks', $customTaskService);
```

### File References
- `src/AsanaClient.php`: Main client class that instantiates services

### API Spec Validation
This is a client-side architecture concern and doesn't directly relate to API specification compliance. However, a proper dependency injection system makes it easier to adapt to API changes or extensions.

### Critical Evaluation
- **Actual Impact**: Medium - Manual dependency instantiation makes the code less flexible and harder to test
- **Priority Level**: High - Should be addressed early to enable other improvements
- **Implementation Status**: Not implemented - Current code uses manual instantiation
- **Spec Compliance**: N/A - This is a client-side architecture concern
- **Difficulty/Complexity**: High - Requires implementing service container patterns, refactoring constructor dependencies throughout the codebase, and maintaining backward compatibility

### Recommended Action
Implement a simple service container or dependency injection pattern to manage service instances. Allow for service registration and replacement to improve testability and flexibility.

## 5. Refactor error handling to be more consistent

### Problem Statement
Error handling is inconsistent across the codebase, with different approaches to exception handling, error reporting, and recovery strategies.

### Code Examples

#### Current Implementation:
```php
// Inconsistent error handling
// In ApiClient.php
public function request($method, $endpoint, $params = [])
{
    try {
        $response = $this->httpClient->request($method, $endpoint, $params);
        return json_decode($response->getBody(), true);
    } catch (RequestException $e) {
        throw new AsanaApiException($e->getMessage(), $e->getCode(), $e);
    }
}

// In OAuthHandler.php
public function handleCallback($code)
{
    try {
        $token = $this->provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);
        return $token;
    } catch (\Exception $e) {
        // Different exception type, different error details
        throw new OAuthCallbackException('OAuth callback failed: ' . $e->getMessage());
    }
}

// In TaskApiService.php
public function createTask($data)
{
    // No try-catch, errors bubble up
    return $this->client->request('POST', 'tasks', ['json' => ['data' => $data]]);
}
```

#### Expected Implementation:
```php
// Consistent error handling with exception hierarchy
// In Exceptions/AsanaException.php
abstract class AsanaException extends \Exception
{
    // Base exception class for all Asana client exceptions
}

// In Exceptions/ApiException.php
class ApiException extends AsanaException
{
    private $requestData;
    private $responseData;
    
    public function __construct($message, $code, $requestData, $responseData = null, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->requestData = $requestData;
        $this->responseData = $responseData;
    }
    
    public function getRequestData()
    {
        return $this->requestData;
    }
    
    public function getResponseData()
    {
        return $this->responseData;
    }
}

// In Exceptions/AuthException.php
class AuthException extends AsanaException
{
    // Authentication-specific exception handling
}

// In ApiClient.php
public function request($method, $endpoint, $params = [])
{
    try {
        $response = $this->httpClient->request($method, $endpoint, $params);
        $data = json_decode($response->getBody(), true);
        
        // Check for API errors in response
        if (isset($data['errors'])) {
            throw new ApiException(
                $data['errors'][0]['message'] ?? 'API error',
                $response->getStatusCode(),
                ['method' => $method, 'endpoint' => $endpoint, 'params' => $params],
                $data
            );
        }
        
        return $data;
    } catch (RequestException $e) {
        $response = $e->getResponse();
        $responseData = $response ? json_decode($response->getBody(), true) : null;
        
        throw new ApiException(
            $e->getMessage(),
            $e->getCode(),
            ['method' => $method, 'endpoint' => $endpoint, 'params' => $params],
            $responseData,
            $e
        );
    }
}

// In OAuthHandler.php
public function handleCallback($code)
{
    try {
        $token = $this->provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);
        return $token;
    } catch (\Exception $e) {
        throw new AuthException('OAuth callback failed: ' . $e->getMessage(), 0, $e);
    }
}
```

### File References
- `src/Http/ApiClient.php`: Contains API request error handling
- `src/Auth/OAuthHandler.php`: Contains OAuth error handling
- `src/Exceptions/`: Directory for exception classes

### API Spec Validation
The Asana API returns errors in a consistent format with error codes and messages. The client's error handling should align with this format and provide meaningful information about API errors.

### Critical Evaluation
- **Actual Impact**: High - Inconsistent error handling makes debugging difficult and can lead to unhandled errors
- **Priority Level**: High - Should be addressed to improve reliability and developer experience
- **Implementation Status**: Partially implemented - Some error handling exists but is inconsistent
- **Spec Compliance**: Partial - Current error handling may not fully capture API error details
- **Difficulty/Complexity**: Medium - Requires creating exception hierarchy and refactoring existing error handling, but follows established exception handling patterns

### Recommended Action
Create a consistent exception hierarchy with base exception classes for different error categories (API, authentication, validation). Ensure all components use this hierarchy and provide detailed error information.