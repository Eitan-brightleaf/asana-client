# Security Improvements

This document outlines critical security enhancements needed for the Asana Client PHP library. Each item includes detailed explanations, code examples, and validation against API specifications.

## 1. Implement rate limiting handling

### Problem Statement
The client does not properly handle rate limiting responses from the Asana API, which can lead to request failures during high traffic periods.

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
        throw new AsanaApiException($e->getMessage(), $e->getCode(), $e);
    }
}
```

#### Expected Implementation:
```php
// In Http/ApiClient.php
public function request($method, $endpoint, $params = [], $retryCount = 0)
{
    try {
        $response = $this->httpClient->request($method, $endpoint, $params);
        return json_decode($response->getBody(), true);
    } catch (RequestException $e) {
        $response = $e->getResponse();
        
        // Handle rate limiting (429 Too Many Requests)
        if ($response && $response->getStatusCode() === 429 && $retryCount < $this->maxRetries) {
            $retryAfter = $response->getHeaderLine('Retry-After');
            $sleepTime = $retryAfter ? (int)$retryAfter : (2 ** $retryCount);
            
            sleep($sleepTime);
            return $this->request($method, $endpoint, $params, $retryCount + 1);
        }
        
        throw new AsanaApiException($e->getMessage(), $e->getCode(), $e);
    }
}
```

### File References
- `src/Http/ApiClient.php`: Contains the HTTP request handling logic

### API Spec Validation
The Asana API uses rate limiting and returns 429 status codes with Retry-After headers when limits are exceeded, as documented in the API specification.

### Critical Evaluation
- **Actual Impact**: Medium - Without proper rate limiting handling, applications may experience failures during high traffic periods
- **Priority Level**: High - Should be addressed soon to ensure reliability
- **Implementation Status**: Not implemented - Current code does not handle rate limiting responses
- **Spec Compliance**: Required - The Asana API documentation specifies rate limiting behavior
- **Difficulty/Complexity**: Medium - Requires implementing retry logic with exponential backoff and proper error handling, but follows established patterns

### Recommended Action
Implement exponential backoff retry mechanism for rate-limited requests. Add configuration options for maximum retry attempts and initial backoff time.

## 2. Add input validation for all public methods

### Problem Statement
The client lacks comprehensive input validation for API method parameters, which could lead to unexpected errors or security vulnerabilities.

### Code Examples

#### Current Implementation:
```php
// In Api/TaskApiService.php
public function createTask($data)
{
    return $this->client->request('POST', 'tasks', ['json' => ['data' => $data]]);
}

public function updateTask($taskId, $data)
{
    return $this->client->request('PUT', "tasks/{$taskId}", ['json' => ['data' => $data]]);
}
```

#### Expected Implementation:
```php
// In Api/TaskApiService.php
public function createTask($data)
{
    $this->validateTaskData($data);
    return $this->client->request('POST', 'tasks', ['json' => ['data' => $data]]);
}

public function updateTask($taskId, $data)
{
    if (empty($taskId) || !is_string($taskId)) {
        throw new InvalidArgumentException('Task ID must be a non-empty string');
    }
    
    $this->validateTaskData($data);
    return $this->client->request('PUT', "tasks/{$taskId}", ['json' => ['data' => $data]]);
}

private function validateTaskData($data)
{
    if (!is_array($data)) {
        throw new InvalidArgumentException('Task data must be an array');
    }
    
    // Validate required fields
    if (isset($data['name']) && !is_string($data['name'])) {
        throw new InvalidArgumentException('Task name must be a string');
    }
    
    if (isset($data['due_on']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['due_on'])) {
        throw new InvalidArgumentException('Due date must be in YYYY-MM-DD format');
    }
    
    // Additional validation as needed
}
```

### File References
- `src/Api/TaskApiService.php`: Example service with methods that need validation
- All other API service classes in `src/Api/`

### API Spec Validation
The Asana API specification defines expected data types and formats for all parameters. Input validation should ensure that client-side data conforms to these specifications before sending requests.

### Critical Evaluation
- **Actual Impact**: Medium - Improper input validation can lead to unexpected errors and potential security issues
- **Priority Level**: High - Should be addressed to improve reliability and security
- **Implementation Status**: Minimal - Current code has limited or no input validation
- **Spec Compliance**: Required - The API specification defines expected data types and formats
- **Difficulty/Complexity**: Medium - Requires implementing comprehensive validation logic across all API service classes and understanding API specification requirements

### Recommended Action
Implement comprehensive input validation for all public methods across all API service classes. Use the API specification as a reference for expected data types and formats.