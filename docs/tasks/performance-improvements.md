# Performance Improvements

This document outlines performance enhancements needed for the Asana Client PHP library. Each item includes detailed explanations, code examples, and validation against API specifications.

## 1. Implement request batching

### Problem Statement
The current implementation makes individual API requests for each operation, which can be inefficient when multiple operations need to be performed. Asana's API supports batching multiple requests into a single HTTP request, which can significantly reduce overhead and improve performance.

### Code Examples

#### Current Implementation:
```php
// In client code
$client = new AsanaClient($clientId, $clientSecret, $redirectUri);

// Multiple separate API requests
$task1 = $client->tasks()->getTask('123456');
$task2 = $client->tasks()->getTask('789012');
$project = $client->projects()->getProject('345678');
$user = $client->users()->getUser('901234');
```

#### Expected Implementation:
```php
// In src/Http/BatchRequest.php
class BatchRequest
{
    private $requests = [];
    private $client;
    
    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }
    
    public function add($method, $path, $options = [])
    {
        $this->requests[] = [
            'method' => $method,
            'relative_path' => $path,
            'options' => $options
        ];
        
        // Return the index of this request for later reference
        return count($this->requests) - 1;
    }
    
    public function execute()
    {
        if (empty($this->requests)) {
            return [];
        }
        
        $batchData = ['data' => []];
        
        foreach ($this->requests as $request) {
            $batchData['data'][] = [
                'method' => $request['method'],
                'relative_path' => $request['relative_path'],
                'data' => $request['options']['json']['data'] ?? null,
                'params' => $request['options']['query'] ?? null
            ];
        }
        
        $response = $this->client->request('POST', 'batch', ['json' => $batchData]);
        
        return $response;
    }
}

// In AsanaClient.php
public function batch()
{
    return new BatchRequest($this->apiClient);
}

// In client code
$client = new AsanaClient($clientId, $clientSecret, $redirectUri);

// Create a batch request
$batch = $client->batch();

// Add multiple requests to the batch
$taskRequest1 = $batch->add('GET', 'tasks/123456');
$taskRequest2 = $batch->add('GET', 'tasks/789012');
$projectRequest = $batch->add('GET', 'projects/345678');
$userRequest = $batch->add('GET', 'users/901234');

// Execute all requests in a single HTTP request
$results = $batch->execute();

// Access the results
$task1 = $results['data'][$taskRequest1];
$task2 = $results['data'][$taskRequest2];
$project = $results['data'][$projectRequest];
$user = $results['data'][$userRequest];
```

### File References
- `src/Http/BatchRequest.php`: New class for handling batch requests
- `src/AsanaClient.php`: Main client class that needs a batch method

### API Spec Validation
The Asana API supports batch requests through the `/batch` endpoint, as documented in the API specification. The implementation should follow the format specified in the API documentation:

```json
{
  "data": [
    {
      "method": "GET",
      "relative_path": "tasks/123456"
    },
    {
      "method": "GET",
      "relative_path": "tasks/789012"
    }
  ]
}
```

### Critical Evaluation
- **Actual Impact**: High - Without batching, applications making multiple API requests experience significant overhead
- **Priority Level**: High - Should be addressed to improve performance for applications making multiple requests
- **Implementation Status**: Not implemented - Current code makes individual requests for each operation
- **Spec Compliance**: Required - The Asana API provides a batch endpoint that should be utilized
- **Difficulty/Complexity**: High - Requires implementing new BatchRequest class, understanding Asana API batch endpoint format, and handling complex response parsing

### Recommended Action
Implement a BatchRequest class that allows multiple requests to be batched into a single HTTP request. Add a method to the AsanaClient class to create and manage batch requests.

## 2. Optimize HTTP client configuration

### Problem Statement
The current HTTP client configuration may not be optimized for performance, particularly for applications that make many API requests. Proper configuration of connection pooling, timeouts, and other HTTP client settings can significantly improve performance.

### Code Examples

#### Current Implementation:
```php
// In Http/ApiClient.php
public function __construct($baseUrl, $timeout = 30)
{
    $this->httpClient = new Client([
        'base_uri' => $baseUrl,
        'timeout' => $timeout
    ]);
}
```

#### Expected Implementation:
```php
// In Http/ApiClient.php
public function __construct($baseUrl, $config = [])
{
    $defaultConfig = [
        'timeout' => 30,
        'connect_timeout' => 10,
        'read_timeout' => 30,
        'http_errors' => true,
        'headers' => [
            'User-Agent' => 'AsanaClient PHP/' . PHP_VERSION,
            'Accept' => 'application/json'
        ],
        'max_retries' => 3,
        'retry_delay' => 1,
        'pool_size' => 25
    ];
    
    $config = array_merge($defaultConfig, $config);
    
    // Configure connection pooling
    $handlerStack = HandlerStack::create();
    
    // Add retry middleware
    $handlerStack->push(Middleware::retry(
        function ($retries, $request, $response, $exception) use ($config) {
            // Retry on connection errors or 5xx server errors
            if ($retries >= $config['max_retries']) {
                return false;
            }
            
            if ($exception instanceof ConnectException) {
                return true;
            }
            
            if ($response && $response->getStatusCode() >= 500) {
                return true;
            }
            
            return false;
        },
        function ($retries) use ($config) {
            // Exponential backoff
            return $config['retry_delay'] * (2 ** $retries);
        }
    ));
    
    $this->httpClient = new Client([
        'base_uri' => $baseUrl,
        'timeout' => $config['timeout'],
        'connect_timeout' => $config['connect_timeout'],
        'read_timeout' => $config['read_timeout'],
        'http_errors' => $config['http_errors'],
        'headers' => $config['headers'],
        'handler' => $handlerStack,
        'curl' => [
            CURLOPT_TCP_KEEPALIVE => 1,
            CURLOPT_TCP_KEEPIDLE => 60,
            CURLOPT_MAXCONNECTS => $config['pool_size']
        ]
    ]);
}
```

### File References
- `src/Http/ApiClient.php`: API client that needs optimized HTTP configuration

### API Spec Validation
HTTP client configuration is a client-side optimization that doesn't directly relate to API specification compliance. However, proper configuration ensures reliable communication with the API.

### Critical Evaluation
- **Actual Impact**: Medium - Suboptimal HTTP client configuration can lead to performance issues, especially under high load
- **Priority Level**: Medium - Should be addressed to improve performance for applications making many requests
- **Implementation Status**: Minimal - Current configuration is basic and may not be optimized for performance
- **Spec Compliance**: N/A - This is a client-side optimization
- **Difficulty/Complexity**: Medium - Requires understanding of Guzzle HTTP client configuration options and performance tuning, but follows established patterns

### Recommended Action
Optimize the HTTP client configuration with appropriate connection pooling, timeout settings, and retry logic. Make these settings configurable to accommodate different usage patterns.

## 3. Implement asynchronous requests

### Problem Statement
The current implementation only supports synchronous requests, which can be inefficient when multiple independent requests need to be made. Implementing asynchronous requests would allow multiple operations to be performed concurrently, reducing overall execution time.

### Code Examples

#### Current Implementation:
```php
// In client code
// Sequential requests
$task1 = $client->tasks()->getTask('123456');
$task2 = $client->tasks()->getTask('789012');
$project = $client->projects()->getProject('345678');
// Total time = sum of individual request times
```

#### Expected Implementation:
```php
// In Http/AsyncApiClient.php
class AsyncApiClient extends ApiClient
{
    /**
     * Make an asynchronous request to the API.
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $endpoint API endpoint
     * @param array $params Request parameters
     * @return PromiseInterface Promise that resolves to the API response
     */
    public function requestAsync($method, $endpoint, $params = [])
    {
        try {
            $promise = $this->httpClient->requestAsync($method, $endpoint, $params);
            
            return $promise->then(
                function ($response) {
                    return json_decode($response->getBody(), true);
                },
                function ($exception) {
                    throw new AsanaApiException($exception->getMessage(), $exception->getCode(), $exception);
                }
            );
        } catch (Exception $e) {
            throw new AsanaApiException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

// In Api/AsyncTaskApiService.php
class AsyncTaskApiService extends TaskApiService
{
    /**
     * Get a task asynchronously.
     *
     * @param string $taskId The task ID to get
     * @param array $options Request options
     * @return PromiseInterface Promise that resolves to the task data
     */
    public function getTaskAsync($taskId, $options = [])
    {
        return $this->client->requestAsync('GET', "tasks/{$taskId}", ['query' => $options]);
    }
    
    // Other async methods...
}

// In AsanaClient.php
/**
 * Get the async task API service.
 *
 * @return AsyncTaskApiService
 */
public function asyncTasks()
{
    if (!isset($this->services['asyncTasks'])) {
        $this->services['asyncTasks'] = new AsyncTaskApiService($this->apiClient);
    }
    
    return $this->services['asyncTasks'];
}

// In client code
// Create promises for concurrent requests
$taskPromise1 = $client->asyncTasks()->getTaskAsync('123456');
$taskPromise2 = $client->asyncTasks()->getTaskAsync('789012');
$projectPromise = $client->asyncProjects()->getProjectAsync('345678');

// Wait for all promises to complete
$results = Promise\Utils::unwrap([
    'task1' => $taskPromise1,
    'task2' => $taskPromise2,
    'project' => $projectPromise
]);

// Access results
$task1 = $results['task1'];
$task2 = $results['task2'];
$project = $results['project'];
// Total time ≈ max(individual request times)
```

### File References
- `src/Http/AsyncApiClient.php`: New class for handling asynchronous requests
- `src/Api/AsyncTaskApiService.php`: Example async service class
- `src/AsanaClient.php`: Main client class that needs async service methods

### API Spec Validation
Asynchronous requests are a client-side optimization that doesn't directly relate to API specification compliance. The implementation should still ensure that all requests conform to the API specification.

### Critical Evaluation
- **Actual Impact**: Medium - Synchronous requests can lead to longer execution times when multiple independent requests are needed
- **Priority Level**: Medium - Should be addressed to improve performance for applications making multiple requests
- **Implementation Status**: Not implemented - Current code only supports synchronous requests
- **Spec Compliance**: N/A - This is a client-side optimization
- **Difficulty/Complexity**: High - Requires implementing complex asynchronous programming patterns, creating async versions of all API services, and handling promise-based workflows

### Recommended Action
Implement asynchronous request capabilities using Guzzle's promise-based API. Add async versions of API service methods and provide utilities for working with promises.