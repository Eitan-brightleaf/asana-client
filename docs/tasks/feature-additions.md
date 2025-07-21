# Feature Additions

This document outlines feature enhancements needed for the Asana Client PHP library. Each item includes detailed explanations, code examples, and validation against API specifications.

## 1. Add support for webhooks

### Problem Statement
The current library does not support Asana's webhook functionality, which allows applications to receive real-time notifications when resources change. Adding webhook support would enable applications to respond to changes in Asana without polling the API.

### Code Examples

#### Current Implementation:
```php
// No webhook support exists in the current codebase
```

#### Expected Implementation:
```php
// In src/Api/WebhookApiService.php
/**
 * Service for interacting with Asana Webhooks API endpoints.
 * 
 * This class provides methods for creating, retrieving, and deleting webhooks.
 * 
 * @link https://developers.asana.com/docs/webhooks
 */
class WebhookApiService
{
    private $client;

    /**
     * Creates a new WebhookApiService instance.
     * 
     * @param ApiClient $client The API client to use for requests
     */
    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * Create a webhook.
     * 
     * Establishes a webhook that will send an HTTP POST request to the specified target
     * when the specified resource changes.
     * 
     * @param array $data Webhook data:
     *                    - resource: (string) A resource ID to subscribe to. The resource can be a task or project.
     *                    - target: (string) The URL to receive the HTTP POST.
     *                    - filters: (array) Optional filters to apply to the webhook
     * 
     * @return array The API response containing the created webhook:
     *               - data: (array) The webhook object
     * 
     * @throws AsanaApiException If the API request fails
     * 
     * @link https://developers.asana.com/docs/establish-a-webhook
     */
    public function createWebhook(array $data): array
    {
        return $this->client->request('POST', 'webhooks', ['json' => ['data' => $data]]);
    }

    /**
     * Get all webhooks.
     * 
     * Returns the compact representation of all webhooks your app has registered
     * for the authenticated user in the given workspace.
     * 
     * @param string $workspaceId The workspace ID to query for webhooks
     * @param array $options Additional options:
     *                       - limit: (int) Results per page (1-100)
     *                       - offset: (string) Pagination offset
     * 
     * @return array The API response containing the list of webhooks:
     *               - data: (array) The list of webhook objects
     *               - next_page: (array|null) Pagination information
     * 
     * @throws AsanaApiException If the API request fails
     * 
     * @link https://developers.asana.com/docs/get-multiple-webhooks
     */
    public function getWebhooks(string $workspaceId, array $options = []): array
    {
        $options['workspace'] = $workspaceId;
        return $this->client->request('GET', 'webhooks', ['query' => $options]);
    }

    /**
     * Get a specific webhook by ID.
     * 
     * Returns the full record for the given webhook.
     * 
     * @param string $webhookId The webhook ID to get
     * 
     * @return array The API response containing the webhook:
     *               - data: (array) The webhook object
     * 
     * @throws AsanaApiException If the API request fails
     * 
     * @link https://developers.asana.com/docs/get-a-webhook
     */
    public function getWebhook(string $webhookId): array
    {
        return $this->client->request('GET', "webhooks/{$webhookId}");
    }

    /**
     * Delete a webhook.
     * 
     * This method permanently removes a webhook. Note that it may be possible to receive
     * a few notifications after the webhook is deleted, due to the asynchronous nature
     * of the webhook system.
     * 
     * @param string $webhookId The webhook ID to delete
     * 
     * @return array The API response:
     *               - data: (array) Empty object
     * 
     * @throws AsanaApiException If the API request fails
     * 
     * @link https://developers.asana.com/docs/delete-a-webhook
     */
    public function deleteWebhook(string $webhookId): array
    {
        return $this->client->request('DELETE', "webhooks/{$webhookId}");
    }

    /**
     * Verify a webhook request from Asana.
     * 
     * Asana sends an X-Hook-Signature header with each webhook request.
     * This method verifies that the signature is valid.
     * 
     * @param string $requestBody The raw request body
     * @param string $signature The X-Hook-Signature header value
     * @param string $secret The webhook secret
     * 
     * @return bool True if the signature is valid, false otherwise
     */
    public function verifyWebhookRequest(string $requestBody, string $signature, string $secret): bool
    {
        $calculatedSignature = hash_hmac('sha256', $requestBody, $secret);
        return hash_equals($calculatedSignature, $signature);
    }

    /**
     * Handle a webhook event.
     * 
     * Processes a webhook event from Asana, verifying the signature and
     * returning the parsed event data.
     * 
     * @param string $requestBody The raw request body
     * @param string $signature The X-Hook-Signature header value
     * @param string $secret The webhook secret
     * 
     * @return array|null The parsed event data, or null if the signature is invalid
     */
    public function handleWebhookEvent(string $requestBody, string $signature, string $secret): ?array
    {
        if (!$this->verifyWebhookRequest($requestBody, $signature, $secret)) {
            return null;
        }

        $eventData = json_decode($requestBody, true);
        return $eventData;
    }
}

// In AsanaClient.php
/**
 * Get the webhook API service.
 * 
 * @return WebhookApiService
 */
public function webhooks(): WebhookApiService
{
    if (!isset($this->services['webhooks'])) {
        $this->services['webhooks'] = new WebhookApiService($this->apiClient);
    }

    return $this->services['webhooks'];
}

// Example usage in application code
// Creating a webhook
$webhookData = [
    'resource' => '12345', // Task or project ID
    'target' => 'https://example.com/webhook-handler'
];
$webhook = $client->webhooks()->createWebhook($webhookData);
$webhookId = $webhook['data']['gid'];

// Handling a webhook event in webhook-handler.php
$requestBody = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HOOK_SIGNATURE'] ?? '';
$secret = getenv('ASANA_WEBHOOK_SECRET');

$event = $client->webhooks()->handleWebhookEvent($requestBody, $signature, $secret);

if ($event) {
    // Process the event
    $events = $event['events'] ?? [];
    foreach ($events as $eventData) {
        $resourceId = $eventData['resource']['gid'] ?? '';
        $action = $eventData['action'] ?? '';

        if ($action === 'changed' && !empty($resourceId)) {
            // Fetch the updated resource
            $task = $client->tasks()->getTask($resourceId);
            // Process the updated task
        }
    }

    // Respond with 200 OK
    http_response_code(200);
    echo json_encode(['status' => 'success']);
} else {
    // Invalid signature
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
}
```

### File References
- `src/Api/WebhookApiService.php`: New class for webhook functionality
- `src/AsanaClient.php`: Main client class that needs a webhooks method

### API Spec Validation
The Asana API supports webhooks as documented in the API specification. The implementation should follow the format specified in the API documentation:

1. Webhook creation requires a resource ID and target URL
2. Webhook events include an X-Hook-Signature header for verification
3. Webhook events contain an array of events with resource IDs and action types

### Critical Evaluation
- **Actual Impact**: High - Without webhook support, applications must poll the API for changes, which is inefficient
- **Priority Level**: High - Should be addressed to enable real-time notifications
- **Implementation Status**: Not implemented - Current code has no webhook support
- **Spec Compliance**: Required - The Asana API provides webhook endpoints that should be utilized
- **Difficulty/Complexity**: High - Requires implementing webhook creation/management, security verification with HMAC signatures, and event handling patterns

### Recommended Action
Implement a WebhookApiService class that provides methods for creating, retrieving, and deleting webhooks, as well as verifying and handling webhook events. Add a webhooks method to the AsanaClient class to access this service.

## 2. Implement cursor-based pagination helpers

### Problem Statement
The current implementation lacks helpers for cursor-based pagination, which is the recommended pagination method for Asana's API. This makes it difficult to efficiently retrieve large collections of resources.

### Code Examples

#### Current Implementation:
```php
// In client code
// Manual pagination handling
$options = ['limit' => 100];
$allTasks = [];

do {
    $response = $client->tasks()->getTasks($options);
    $allTasks = array_merge($allTasks, $response['data']);

    // Update offset for next page
    $options['offset'] = isset($response['next_page']['offset']) ? 
        $response['next_page']['offset'] : null;

} while ($options['offset'] !== null);
```

#### Expected Implementation:
```php
// In src/Utils/PaginationIterator.php
/**
 * Iterator for paginated API responses.
 * 
 * This class provides an iterator interface for paginated API responses,
 * automatically handling pagination using either offset or cursor-based pagination.
 */
class PaginationIterator implements \Iterator
{
    private $apiService;
    private $method;
    private $params;
    private $currentPage;
    private $position = 0;
    private $items = [];
    private $nextPageParams = null;
    private $fetchedAllPages = false;

    /**
     * Creates a new PaginationIterator instance.
     * 
     * @param object $apiService The API service to use for requests
     * @param string $method The method to call on the API service
     * @param array $params The parameters to pass to the method
     * @param int $limit The number of items to fetch per page
     */
    public function __construct($apiService, string $method, array $params = [], int $limit = 100)
    {
        $this->apiService = $apiService;
        $this->method = $method;
        $this->params = array_merge($params, ['limit' => $limit]);
    }

    /**
     * Rewind the iterator to the first item.
     */
    public function rewind(): void
    {
        $this->position = 0;
        $this->items = [];
        $this->nextPageParams = null;
        $this->fetchedAllPages = false;
        $this->fetchNextPage();
    }

    /**
     * Check if the current position is valid.
     * 
     * @return bool True if the current position is valid
     */
    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }

    /**
     * Get the current item.
     * 
     * @return mixed The current item
     */
    public function current()
    {
        return $this->items[$this->position];
    }

    /**
     * Get the current position.
     * 
     * @return int The current position
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Move to the next item.
     */
    public function next(): void
    {
        $this->position++;

        // If we've reached the end of the current page and there are more pages,
        // fetch the next page
        if (!$this->valid() && !$this->fetchedAllPages) {
            $this->fetchNextPage();
        }
    }

    /**
     * Fetch the next page of results.
     */
    private function fetchNextPage(): void
    {
        $params = $this->params;

        if ($this->nextPageParams) {
            $params = array_merge($params, $this->nextPageParams);
        }

        $response = call_user_func([$this->apiService, $this->method], $params);

        if (isset($response['data']) && is_array($response['data'])) {
            $this->items = $response['data'];
            $this->position = 0;

            // Handle offset-based pagination
            if (isset($response['next_page']['offset'])) {
                $this->nextPageParams = ['offset' => $response['next_page']['offset']];
            }
            // Handle cursor-based pagination
            elseif (isset($response['next_page']['path'])) {
                // Extract cursor from path
                $path = $response['next_page']['path'];
                parse_str(parse_url($path, PHP_URL_QUERY), $queryParams);

                if (isset($queryParams['cursor'])) {
                    $this->nextPageParams = ['cursor' => $queryParams['cursor']];
                } else {
                    $this->fetchedAllPages = true;
                }
            } else {
                $this->fetchedAllPages = true;
            }
        } else {
            $this->items = [];
            $this->fetchedAllPages = true;
        }
    }

    /**
     * Get all items as an array.
     * 
     * @return array All items
     */
    public function toArray(): array
    {
        $items = [];

        foreach ($this as $item) {
            $items[] = $item;
        }

        return $items;
    }
}

// In src/Api/TaskApiService.php
/**
 * Get a paginated iterator for tasks.
 * 
 * @param array $options Filter options for the request
 * @param int $limit The number of items to fetch per page
 * @return PaginationIterator An iterator for the tasks
 */
public function getTasksIterator(array $options = [], int $limit = 100): PaginationIterator
{
    return new PaginationIterator($this, 'getTasks', $options, $limit);
}

// In client code
// Using the iterator
$taskIterator = $client->tasks()->getTasksIterator(['project' => 'PROJECT_ID']);

// Option 1: Iterate through all tasks
foreach ($taskIterator as $task) {
    // Process each task
    processTask($task);
}

// Option 2: Convert to array (fetches all pages)
$allTasks = $taskIterator->toArray();

// Option 3: Process tasks in batches with a callback
$client->tasks()->getAllTasks(
    ['project' => 'PROJECT_ID'],
    100,
    function ($batch) {
        foreach ($batch as $task) {
            // Process each task
            processTask($task);
        }
    }
);
```

### File References
- `src/Utils/PaginationIterator.php`: New class for pagination handling
- `src/Api/TaskApiService.php`: Example service class that needs pagination helpers
- Other API service classes that handle collections

### API Spec Validation
The Asana API supports both offset-based and cursor-based pagination, as documented in the API specification. The implementation should handle both pagination types and follow the format specified in the API documentation:

1. Offset-based pagination uses the `offset` parameter and returns `next_page.offset` in the response
2. Cursor-based pagination returns `next_page.path` with a cursor parameter in the response

### Critical Evaluation
- **Actual Impact**: Medium - Without pagination helpers, applications must implement their own pagination logic
- **Priority Level**: Medium - Should be addressed to improve developer experience
- **Implementation Status**: Not implemented - Current code has no pagination helpers
- **Spec Compliance**: Required - The library should properly support the API's pagination capabilities
- **Difficulty/Complexity**: Medium - Requires implementing iterator patterns and handling both offset-based and cursor-based pagination logic

### Recommended Action
Implement a PaginationIterator class that provides an iterator interface for paginated API responses, automatically handling both offset-based and cursor-based pagination. Add methods to API service classes to return iterators for collections.

## 3. Create model classes for Asana resources

### Problem Statement
The current implementation returns raw API responses as arrays, which lacks type safety and makes it harder to work with Asana resources. Creating model classes for Asana resources would provide a more structured and type-safe way to interact with the API.

### Code Examples

#### Current Implementation:
```php
// In client code
// Working with raw arrays
$task = $client->tasks()->getTask('12345');
$taskName = $task['data']['name'];
$taskDueDate = $task['data']['due_on'];
$assigneeId = $task['data']['assignee']['gid'] ?? null;
```

#### Expected Implementation:
```php
// In src/Models/Task.php
/**
 * Model class for an Asana task.
 */
class Task
{
    private $id;
    private $name;
    private $notes;
    private $completed;
    private $dueOn;
    private $dueAt;
    private $assignee;
    private $assigneeStatus;
    private $createdAt;
    private $modifiedAt;
    private $projects = [];
    private $memberships = [];
    private $tags = [];
    private $customFields = [];
    private $rawData;

    /**
     * Creates a new Task instance from API data.
     * 
     * @param array $data The task data from the API
     * 
     * @return self
     */
    public static function fromApiData(array $data): self
    {
        $task = new self();
        $task->id = $data['gid'] ?? null;
        $task->name = $data['name'] ?? null;
        $task->notes = $data['notes'] ?? null;
        $task->completed = $data['completed'] ?? false;
        $task->dueOn = $data['due_on'] ?? null;
        $task->dueAt = $data['due_at'] ?? null;
        $task->assigneeStatus = $data['assignee_status'] ?? null;
        $task->createdAt = $data['created_at'] ?? null;
        $task->modifiedAt = $data['modified_at'] ?? null;

        // Handle assignee
        if (isset($data['assignee']) && is_array($data['assignee'])) {
            $task->assignee = User::fromApiData($data['assignee']);
        }

        // Handle projects
        if (isset($data['projects']) && is_array($data['projects'])) {
            foreach ($data['projects'] as $projectData) {
                $task->projects[] = Project::fromApiData($projectData);
            }
        }

        // Handle memberships
        if (isset($data['memberships']) && is_array($data['memberships'])) {
            foreach ($data['memberships'] as $membershipData) {
                $task->memberships[] = TaskMembership::fromApiData($membershipData);
            }
        }

        // Handle tags
        if (isset($data['tags']) && is_array($data['tags'])) {
            foreach ($data['tags'] as $tagData) {
                $task->tags[] = Tag::fromApiData($tagData);
            }
        }

        // Handle custom fields
        if (isset($data['custom_fields']) && is_array($data['custom_fields'])) {
            foreach ($data['custom_fields'] as $customFieldData) {
                $task->customFields[] = CustomField::fromApiData($customFieldData);
            }
        }

        // Store the raw data
        $task->rawData = $data;

        return $task;
    }

    /**
     * Get the task ID.
     * 
     * @return string|null The task ID
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Get the task name.
     * 
     * @return string|null The task name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get the task notes.
     * 
     * @return string|null The task notes
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * Check if the task is completed.
     * 
     * @return bool True if the task is completed
     */
    public function isCompleted(): bool
    {
        return $this->completed;
    }

    /**
     * Get the task due date.
     * 
     * @return string|null The task due date (YYYY-MM-DD)
     */
    public function getDueOn(): ?string
    {
        return $this->dueOn;
    }

    /**
     * Get the task due date and time.
     * 
     * @return string|null The task due date and time (ISO 8601)
     */
    public function getDueAt(): ?string
    {
        return $this->dueAt;
    }

    /**
     * Get the task assignee.
     * 
     * @return User|null The task assignee
     */
    public function getAssignee(): ?User
    {
        return $this->assignee;
    }

    /**
     * Get the task assignee status.
     * 
     * @return string|null The task assignee status
     */
    public function getAssigneeStatus(): ?string
    {
        return $this->assigneeStatus;
    }

    /**
     * Get the task creation date.
     * 
     * @return string|null The task creation date (ISO 8601)
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * Get the task modification date.
     * 
     * @return string|null The task modification date (ISO 8601)
     */
    public function getModifiedAt(): ?string
    {
        return $this->modifiedAt;
    }

    /**
     * Get the task projects.
     * 
     * @return Project[] The task projects
     */
    public function getProjects(): array
    {
        return $this->projects;
    }

    /**
     * Get the task memberships.
     * 
     * @return TaskMembership[] The task memberships
     */
    public function getMemberships(): array
    {
        return $this->memberships;
    }

    /**
     * Get the task tags.
     * 
     * @return Tag[] The task tags
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Get the task custom fields.
     * 
     * @return CustomField[] The task custom fields
     */
    public function getCustomFields(): array
    {
        return $this->customFields;
    }

    /**
     * Get a custom field value by name.
     * 
     * @param string $name The custom field name
     * 
     * @return mixed|null The custom field value, or null if not found
     */
    public function getCustomFieldValueByName(string $name)
    {
        foreach ($this->customFields as $customField) {
            if ($customField->getName() === $name) {
                return $customField->getValue();
            }
        }

        return null;
    }

    /**
     * Get the raw API data.
     * 
     * @return array The raw API data
     */
    public function getRawData(): array
    {
        return $this->rawData;
    }

    /**
     * Convert the task to an array for API requests.
     * 
     * @return array The task data as an array
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'notes' => $this->notes,
            'completed' => $this->completed
        ];

        if ($this->dueOn !== null) {
            $data['due_on'] = $this->dueOn;
        }

        if ($this->dueAt !== null) {
            $data['due_at'] = $this->dueAt;
        }

        if ($this->assignee !== null) {
            $data['assignee'] = $this->assignee->getId();
        }

        return $data;
    }
}

// In src/Api/TaskApiService.php
/**
 * Get a specific task by ID.
 * 
 * @param string $taskId The task ID to get
 * @param array $options Request options
 * 
 * @return Task The task object
 * 
 * @throws AsanaApiException If the API request fails
 */
public function getTask(string $taskId, array $options = []): Task
{
    $response = $this->client->request('GET', "tasks/{$taskId}", ['query' => $options]);
    return Task::fromApiData($response['data']);
}

/**
 * Get multiple tasks.
 * 
 * @param array $options Filter options for the request
 * 
 * @return array The API response containing the list of tasks:
 *               - data: (array) The list of task objects as Task instances
 *               - next_page: (array|null) Pagination information
 * 
 * @throws AsanaApiException If the API request fails
 */
public function getTasks(array $options = []): array
{
    $response = $this->client->request('GET', 'tasks', ['query' => $options]);

    // Convert raw task data to Task objects
    $tasks = [];
    foreach ($response['data'] as $taskData) {
        $tasks[] = Task::fromApiData($taskData);
    }

    // Return the response with Task objects instead of raw data
    $response['data'] = $tasks;
    return $response;
}

// In client code
// Working with model objects
$task = $client->tasks()->getTask('12345');
$taskName = $task->getName();
$taskDueDate = $task->getDueOn();
$assignee = $task->getAssignee();
$assigneeId = $assignee ? $assignee->getId() : null;

// Get a custom field value by name
$priority = $task->getCustomFieldValueByName('Priority');
```

### File References
- `src/Models/Task.php`: New model class for tasks
- `src/Models/Project.php`: New model class for projects
- `src/Models/User.php`: New model class for users
- `src/Models/TaskMembership.php`: New model class for task memberships
- `src/Models/Tag.php`: New model class for tags
- `src/Models/CustomField.php`: New model class for custom fields
- `src/Api/TaskApiService.php`: API service class that needs to return model objects

### API Spec Validation
The model classes should accurately reflect the structure of resources as defined in the API specification, including all properties and relationships.

### Critical Evaluation
- **Actual Impact**: Medium - Working with raw arrays is functional but less convenient and type-safe
- **Priority Level**: Medium - Should be addressed to improve developer experience
- **Implementation Status**: Not implemented - Current code returns raw arrays
- **Spec Compliance**: Enhancement - This is a client-side improvement that makes it easier to work with the API
- **Difficulty/Complexity**: High - Requires creating comprehensive model classes for all API resources, understanding complex resource relationships, and maintaining type safety

### Recommended Action
Create model classes for all Asana resources (tasks, projects, users, etc.) that provide typed properties, getters, and helper methods. Update API service classes to return model objects instead of raw arrays.

## 4. Add event subscription management

### Problem Statement
The current library does not support Asana's event subscription API, which allows applications to receive notifications about changes to resources. Adding event subscription management would enable applications to track changes to resources without polling the API.

### Code Examples

#### Current Implementation:
```php
// No event subscription support exists in the current codebase
```

#### Expected Implementation:
```php
// In src/Api/EventApiService.php
/**
 * Service for interacting with Asana Events API endpoints.
 * 
 * This class provides methods for retrieving events and managing event subscriptions.
 * 
 * @link https://developers.asana.com/docs/events
 */
class EventApiService
{
    private $client;

    /**
     * Creates a new EventApiService instance.
     * 
     * @param ApiClient $client The API client to use for requests
     */
    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * Get events for a resource.
     * 
     * Returns all events that have occurred since the sync token was created.
     * 
     * @param string $resourceId The resource ID to get events for
     * @param string|null $syncToken The sync token to use for pagination
     * 
     * @return array The API response containing the events:
     *               - data: (array) The list of event objects
     *               - sync: (string) The sync token for the next request
     *               - has_more: (bool) Whether there are more events to retrieve
     * 
     * @throws AsanaApiException If the API request fails
     * 
     * @link https://developers.asana.com/docs/get-events-on-a-resource
     */
    public function getEvents(string $resourceId, ?string $syncToken = null): array
    {
        $params = ['resource' => $resourceId];

        if ($syncToken) {
            $params['sync'] = $syncToken;
        }

        return $this->client->request('GET', 'events', ['query' => $params]);
    }

    /**
     * Create an event subscription.
     * 
     * @param array $data Subscription data:
     *                    - resource: (string) A resource ID to subscribe to
     *                    - target: (string) The URL to receive the HTTP POST
     *                    - filters: (array) Optional filters to apply to the subscription
     * 
     * @return array The API response containing the created subscription:
     *               - data: (array) The subscription object
     * 
     * @throws AsanaApiException If the API request fails
     * 
     * @link https://developers.asana.com/docs/establish-a-webhook
     */
    public function createSubscription(array $data): array
    {
        return $this->client->request('POST', 'webhooks', ['json' => ['data' => $data]]);
    }

    /**
     * Get all event subscriptions.
     * 
     * @param string $workspaceId The workspace ID to query for subscriptions
     * @param array $options Additional options:
     *                       - limit: (int) Results per page (1-100)
     *                       - offset: (string) Pagination offset
     * 
     * @return array The API response containing the list of subscriptions:
     *               - data: (array) The list of subscription objects
     *               - next_page: (array|null) Pagination information
     * 
     * @throws AsanaApiException If the API request fails
     * 
     * @link https://developers.asana.com/docs/get-multiple-webhooks
     */
    public function getSubscriptions(string $workspaceId, array $options = []): array
    {
        $options['workspace'] = $workspaceId;
        return $this->client->request('GET', 'webhooks', ['query' => $options]);
    }

    /**
     * Get a specific event subscription by ID.
     * 
     * @param string $subscriptionId The subscription ID to get
     * 
     * @return array The API response containing the subscription:
     *               - data: (array) The subscription object
     * 
     * @throws AsanaApiException If the API request fails
     * 
     * @link https://developers.asana.com/docs/get-a-webhook
     */
    public function getSubscription(string $subscriptionId): array
    {
        return $this->client->request('GET', "webhooks/{$subscriptionId}");
    }

    /**
     * Delete an event subscription.
     * 
     * @param string $subscriptionId The subscription ID to delete
     * 
     * @return array The API response:
     *               - data: (array) Empty object
     * 
     * @throws AsanaApiException If the API request fails
     * 
     * @link https://developers.asana.com/docs/delete-a-webhook
     */
    public function deleteSubscription(string $subscriptionId): array
    {
        return $this->client->request('DELETE', "webhooks/{$subscriptionId}");
    }

    /**
     * Create an EventIterator for polling events.
     * 
     * @param string $resourceId The resource ID to get events for
     * @param string|null $syncToken The sync token to start from
     * 
     * @return EventIterator An iterator for events
     */
    public function getEventIterator(string $resourceId, ?string $syncToken = null): EventIterator
    {
        return new EventIterator($this, $resourceId, $syncToken);
    }
}

// In src/Utils/EventIterator.php
/**
 * Iterator for Asana events.
 * 
 * This class provides an iterator interface for Asana events,
 * automatically handling pagination using sync tokens.
 */
class EventIterator implements \Iterator
{
    private $eventService;
    private $resourceId;
    private $syncToken;
    private $position = 0;
    private $events = [];
    private $hasMore = false;
    private $fetchedAllEvents = false;

    /**
     * Creates a new EventIterator instance.
     * 
     * @param EventApiService $eventService The event API service to use for requests
     * @param string $resourceId The resource ID to get events for
     * @param string|null $syncToken The sync token to start from
     */
    public function __construct(EventApiService $eventService, string $resourceId, ?string $syncToken = null)
    {
        $this->eventService = $eventService;
        $this->resourceId = $resourceId;
        $this->syncToken = $syncToken;
    }

    /**
     * Rewind the iterator to the first event.
     */
    public function rewind(): void
    {
        $this->position = 0;
        $this->events = [];
        $this->fetchedAllEvents = false;
        $this->fetchNextPage();
    }

    /**
     * Check if the current position is valid.
     * 
     * @return bool True if the current position is valid
     */
    public function valid(): bool
    {
        return isset($this->events[$this->position]);
    }

    /**
     * Get the current event.
     * 
     * @return array The current event
     */
    public function current(): array
    {
        return $this->events[$this->position];
    }

    /**
     * Get the current position.
     * 
     * @return int The current position
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Move to the next event.
     */
    public function next(): void
    {
        $this->position++;

        // If we've reached the end of the current page and there are more events,
        // fetch the next page
        if (!$this->valid() && $this->hasMore && !$this->fetchedAllEvents) {
            $this->fetchNextPage();
        }
    }

    /**
     * Fetch the next page of events.
     */
    private function fetchNextPage(): void
    {
        $response = $this->eventService->getEvents($this->resourceId, $this->syncToken);

        if (isset($response['data']) && is_array($response['data'])) {
            $this->events = $response['data'];
            $this->position = 0;
            $this->syncToken = $response['sync'] ?? null;
            $this->hasMore = $response['has_more'] ?? false;

            if (!$this->hasMore) {
                $this->fetchedAllEvents = true;
            }
        } else {
            $this->events = [];
            $this->fetchedAllEvents = true;
        }
    }

    /**
     * Get the current sync token.
     * 
     * @return string|null The current sync token
     */
    public function getSyncToken(): ?string
    {
        return $this->syncToken;
    }

    /**
     * Get all events as an array.
     * 
     * @return array All events
     */
    public function toArray(): array
    {
        $events = [];

        foreach ($this as $event) {
            $events[] = $event;
        }

        return $events;
    }
}

// In AsanaClient.php
/**
 * Get the event API service.
 * 
 * @return EventApiService
 */
public function events(): EventApiService
{
    if (!isset($this->services['events'])) {
        $this->services['events'] = new EventApiService($this->apiClient);
    }

    return $this->services['events'];
}

// Example usage in application code
// Get events for a resource
$resourceId = '12345'; // Task or project ID
$response = $client->events()->getEvents($resourceId);
$events = $response['data'];
$syncToken = $response['sync'];

// Process events
foreach ($events as $event) {
    $action = $event['action'];
    $resource = $event['resource'];

    // Handle the event based on action and resource type
    if ($action === 'changed' && $resource['resource_type'] === 'task') {
        // A task was changed
        $taskId = $resource['gid'];
        $task = $client->tasks()->getTask($taskId);
        // Process the updated task
    }
}

// Use the sync token for the next request to get only new events
$newEvents = $client->events()->getEvents($resourceId, $syncToken);

// Or use the EventIterator for continuous polling
$eventIterator = $client->events()->getEventIterator($resourceId);

// Poll for events every 30 seconds
while (true) {
    foreach ($eventIterator as $event) {
        // Process the event
        processEvent($event);
    }

    // Save the sync token for later use
    $syncToken = $eventIterator->getSyncToken();

    // Wait before polling again
    sleep(30);
}
```

### File References
- `src/Api/EventApiService.php`: New class for event functionality
- `src/Utils/EventIterator.php`: New class for iterating through events
- `src/AsanaClient.php`: Main client class that needs an events method

### API Spec Validation
The Asana API supports events and webhooks as documented in the API specification. The implementation should follow the format specified in the API documentation:

1. Events can be retrieved using a sync token for pagination
2. Webhooks can be created to receive event notifications
3. Events include action and resource information

### Critical Evaluation
- **Actual Impact**: Medium - Without event subscription support, applications must poll the API for changes
- **Priority Level**: Medium - Should be addressed to enable efficient change tracking
- **Implementation Status**: Not implemented - Current code has no event subscription support
- **Spec Compliance**: Required - The Asana API provides events and webhooks endpoints that should be utilized
- **Difficulty/Complexity**: High - Requires implementing event-driven patterns, sync token management, efficient polling mechanisms, and webhook integration

### Recommended Action
Implement an EventApiService class that provides methods for retrieving events and managing event subscriptions. Add an EventIterator class for efficient event polling. Add an events method to the AsanaClient class to access this service.

## 6. Support full API coverage

### Problem Statement
The current library covers only a subset of the Asana API endpoints. A comprehensive analysis of the API specification shows that many important endpoints are not yet implemented. Ensuring full API coverage would make the library more complete and useful for a wider range of applications.

### Comprehensive API Coverage Analysis

Based on the Asana API specification (asana_oas.yaml) and the current implementation, the following endpoints are not yet supported:

#### High Priority Endpoints

1. **Webhooks API**
   - **HTTP Methods**: GET, POST, DELETE
   - **Endpoints**: `/webhooks`, `/webhooks/{webhook_gid}`
   - **Description**: Allows applications to receive real-time notifications when resources change
   - **Use Cases**: Real-time updates, event-driven architecture, integrations with other systems

2. **Events API**
   - **HTTP Methods**: GET
   - **Endpoints**: `/events`
   - **Description**: Provides access to events that occur in Asana
   - **Use Cases**: Activity tracking, audit logging, synchronization with external systems

3. **Teams API**
   - **HTTP Methods**: GET, POST, PUT
   - **Endpoints**: `/teams`, `/teams/{team_gid}`, `/teams/{team_gid}/projects`
   - **Description**: Manages teams within an organization
   - **Use Cases**: Team management, project organization, user permissions

4. **Portfolios API**
   - **HTTP Methods**: GET, POST, PUT, DELETE
   - **Endpoints**: `/portfolios`, `/portfolios/{portfolio_gid}`, `/portfolios/{portfolio_gid}/items`
   - **Description**: Manages portfolios, which provide a high-level view of multiple projects
   - **Use Cases**: Project grouping, status reporting, high-level progress tracking

#### Medium Priority Endpoints

5. **Goals API**
   - **HTTP Methods**: GET, POST, PUT, DELETE
   - **Endpoints**: `/goals`, `/goals/{goal_gid}`
   - **Description**: Manages goals and objectives
   - **Use Cases**: OKR tracking, performance management, strategic planning

6. **Time Tracking API**
   - **HTTP Methods**: GET, POST, PUT, DELETE
   - **Endpoints**: `/time_tracking_entries`, `/time_tracking_entries/{time_tracking_entry_gid}`
   - **Description**: Manages time tracking entries for tasks
   - **Use Cases**: Time tracking, billing, productivity analysis

7. **Project Templates API**
   - **HTTP Methods**: GET, POST
   - **Endpoints**: `/project_templates`, `/project_templates/{project_template_gid}/instantiate_project`
   - **Description**: Manages project templates and creates projects from templates
   - **Use Cases**: Standardized project creation, workflow automation

8. **Batch API**
   - **HTTP Methods**: POST
   - **Endpoints**: `/batch`
   - **Description**: Allows multiple API requests to be made in a single HTTP request
   - **Use Cases**: Performance optimization, reducing API calls, complex operations

#### Lower Priority Endpoints

9. **Status Updates API**
   - **HTTP Methods**: GET, POST, DELETE
   - **Endpoints**: `/status_updates`, `/status_updates/{status_update_gid}`
   - **Description**: Manages status updates for projects and portfolios
   - **Use Cases**: Project status reporting, team communication

10. **User Task Lists API**
    - **HTTP Methods**: GET
    - **Endpoints**: `/user_task_lists`, `/user_task_lists/{user_task_list_gid}`
    - **Description**: Accesses a user's "My Tasks" list
    - **Use Cases**: Personal task management, task assignment

11. **Workspace Memberships API**
    - **HTTP Methods**: GET
    - **Endpoints**: `/workspace_memberships`, `/workspace_memberships/{workspace_membership_gid}`
    - **Description**: Manages user memberships in workspaces
    - **Use Cases**: User access management, organization structure

12. **Team Memberships API**
    - **HTTP Methods**: GET, POST, DELETE
    - **Endpoints**: `/team_memberships`, `/team_memberships/{team_membership_gid}`
    - **Description**: Manages user memberships in teams
    - **Use Cases**: Team composition, access control

13. **Project Memberships API**
    - **HTTP Methods**: GET, POST, DELETE
    - **Endpoints**: `/project_memberships`, `/project_memberships/{project_membership_gid}`
    - **Description**: Manages user memberships in projects
    - **Use Cases**: Project access control, collaboration management

14. **Portfolio Memberships API**
    - **HTTP Methods**: GET, POST, DELETE
    - **Endpoints**: `/portfolio_memberships`, `/portfolio_memberships/{portfolio_membership_gid}`
    - **Description**: Manages user memberships in portfolios
    - **Use Cases**: Portfolio access control, stakeholder management

15. **Custom Types API**
    - **HTTP Methods**: GET
    - **Endpoints**: `/custom_types`, `/custom_types/{custom_type_gid}`
    - **Description**: Manages custom types for objects
    - **Use Cases**: Custom workflows, specialized object types

16. **Typeahead API**
    - **HTTP Methods**: GET
    - **Endpoints**: `/typeahead`
    - **Description**: Provides search functionality for Asana objects
    - **Use Cases**: Auto-completion, search functionality

17. **Audit Log API**
    - **HTTP Methods**: GET
    - **Endpoints**: `/workspaces/{workspace_gid}/audit_log_events`
    - **Description**: Retrieves audit log events for a workspace
    - **Use Cases**: Security monitoring, compliance, activity tracking

18. **Organization Exports API**
    - **HTTP Methods**: GET, POST
    - **Endpoints**: `/organization_exports`, `/organization_exports/{organization_export_gid}`
    - **Description**: Creates and manages organization data exports
    - **Use Cases**: Data backup, migration, analysis

19. **Project Briefs API**
    - **HTTP Methods**: GET, PUT
    - **Endpoints**: `/project_briefs/{project_brief_gid}`
    - **Description**: Manages project briefs (rich text documents)
    - **Use Cases**: Project documentation, requirements management

20. **Rules API**
    - **HTTP Methods**: GET, POST, PUT, DELETE
    - **Endpoints**: `/rule_triggers/{rule_trigger_gid}/run`
    - **Description**: Manages automation rules
    - **Use Cases**: Workflow automation, process standardization

### Code Examples

#### Example Implementation for Webhooks API:

```php
// In src/Api/WebhookApiService.php
class WebhookApiService
{
    private AsanaApiClient $client;

    public function __construct(AsanaApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create a webhook.
     *
     * @param string $resourceGid The resource to subscribe to
     * @param string $target The target URL for webhook delivery
     * @param array $options Additional options
     * @param int $responseType Response type format
     * 
     * @return array The created webhook
     */
    public function createWebhook(
        string $resourceGid,
        string $target,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $data = [
            'resource' => $resourceGid,
            'target' => $target
        ];

        if (isset($options['filters'])) {
            $data['filters'] = $options['filters'];
        }

        return $this->client->request('POST', 'webhooks', ['json' => ['data' => $data]], $responseType);
    }

    /**
     * Get all webhooks for a workspace.
     *
     * @param string $workspaceGid The workspace GID
     * @param array $options Additional options
     * @param int $responseType Response type format
     * 
     * @return array List of webhooks
     */
    public function getWebhooks(
        string $workspaceGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $queryParams = array_merge(['workspace' => $workspaceGid], $options);
        return $this->client->request('GET', 'webhooks', ['query' => $queryParams], $responseType);
    }

    /**
     * Get a specific webhook by ID.
     *
     * @param string $webhookGid The webhook GID
     * @param array $options Additional options
     * @param int $responseType Response type format
     * 
     * @return array The webhook details
     */
    public function getWebhook(
        string $webhookGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request('GET', "webhooks/{$webhookGid}", ['query' => $options], $responseType);
    }

    /**
     * Delete a webhook.
     *
     * @param string $webhookGid The webhook GID
     * @param int $responseType Response type format
     * 
     * @return array Empty response on success
     */
    public function deleteWebhook(
        string $webhookGid,
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request('DELETE', "webhooks/{$webhookGid}", [], $responseType);
    }

    /**
     * Verify a webhook request signature.
     *
     * @param string $requestBody The raw request body
     * @param string $signature The X-Hook-Signature header value
     * @param string $secret The webhook secret
     * 
     * @return bool True if the signature is valid
     */
    public function verifyWebhookSignature(string $requestBody, string $signature, string $secret): bool
    {
        $calculatedSignature = hash_hmac('sha256', $requestBody, $secret);
        return hash_equals($calculatedSignature, $signature);
    }
}

// In AsanaClient.php
/**
 * Get the webhook API service.
 * 
 * @return WebhookApiService
 * @throws TokenInvalidException If the token is invalid or cannot be refreshed
 */
public function webhooks(): WebhookApiService
{
    if (!isset($this->webhooks)) {
        $this->webhooks = new WebhookApiService($this->getApiClient());
    }

    $this->ensureValidToken();

    return $this->webhooks;
}
```

### File References
- `src/Api/`: Directory containing all API service classes
- `src/AsanaClient.php`: Main client class that needs methods for all API services

### API Spec Validation
The Asana API specification (asana_oas.yaml) defines all the endpoints listed above. Implementing these endpoints would ensure full compliance with the API specification.

### Critical Evaluation
- **Actual Impact**: High - Missing endpoints limit the library's usefulness for many applications
- **Priority Level**: High - Should be addressed to make the library more complete and useful
- **Implementation Status**: Partially implemented - Current code covers only a subset of API endpoints
- **Spec Compliance**: Partial - The library should support all endpoints in the API specification
- **Difficulty/Complexity**: High - Requires implementing numerous API service classes, understanding diverse endpoint requirements, and maintaining comprehensive API coverage

### Recommended Action
Implement API service classes for all missing endpoints in the Asana API specification, prioritizing the high-priority endpoints first. Add methods to the AsanaClient class to access these services. Ensure that all API features are properly documented and tested.
