# Testing Improvements

This document outlines testing enhancements needed for the Asana Client PHP library. Each item includes detailed explanations, code examples, and validation against API specifications.

## 1. Increase test coverage for API service classes

### Problem Statement
Currently, only the AsanaClient class has adequate test coverage. The individual API service classes (TaskApiService, ProjectApiService, etc.) lack comprehensive tests, which increases the risk of undetected bugs and regressions.

### Code Examples

#### Current Implementation:
```php
// In tests/AsanaClientTest.php
class AsanaClientTest extends TestCase
{
    public function testWithAccessToken()
    {
        $client = AsanaClient::withAccessToken('client_id', 'client_secret', 'token_data');
        $this->assertInstanceOf(AsanaClient::class, $client);
    }

    public function testWithPAT()
    {
        $client = AsanaClient::withPAT('personal_access_token');
        $this->assertInstanceOf(AsanaClient::class, $client);
    }

    // Other AsanaClient tests...
}

// Missing tests for API service classes
```

#### Expected Implementation:
```php
// In tests/Api/TaskApiServiceTest.php
class TaskApiServiceTest extends TestCase
{
    private $mockClient;
    private $taskService;

    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(ApiClient::class);
        $this->taskService = new TaskApiService($this->mockClient);
    }

    public function testGetTasks()
    {
        $expectedResponse = [
            'data' => [
                ['gid' => '12345', 'name' => 'Test Task 1'],
                ['gid' => '67890', 'name' => 'Test Task 2']
            ]
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tasks', ['query' => ['workspace' => '1234']])
            ->willReturn($expectedResponse);

        $result = $this->taskService->getTasks(['workspace' => '1234']);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testGetTask()
    {
        $taskId = '12345';
        $expectedResponse = [
            'data' => ['gid' => $taskId, 'name' => 'Test Task']
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', "tasks/{$taskId}", ['query' => []])
            ->willReturn($expectedResponse);

        $result = $this->taskService->getTask($taskId);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testCreateTask()
    {
        $taskData = ['name' => 'New Task', 'notes' => 'Task notes'];
        $expectedResponse = [
            'data' => ['gid' => '12345', 'name' => 'New Task', 'notes' => 'Task notes']
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('POST', 'tasks', ['json' => ['data' => $taskData]])
            ->willReturn($expectedResponse);

        $result = $this->taskService->createTask($taskData);
        $this->assertEquals($expectedResponse, $result);
    }

    // Additional tests for other methods...
}

// Similar test classes for other API services
```

### File References
- `tests/AsanaClientTest.php`: Existing tests for the main client
- `tests/Api/TaskApiServiceTest.php`: New tests for TaskApiService
- `tests/Api/ProjectApiServiceTest.php`: New tests for ProjectApiService
- `tests/Api/WorkspaceApiServiceTest.php`: New tests for WorkspaceApiService

### API Spec Validation
Tests should validate that the client correctly implements the API specification. This includes verifying that:
1. Endpoints are correctly formatted
2. Request parameters match the API specification
3. Response handling correctly processes the API's response format

### Critical Evaluation
- **Actual Impact**: High - Lack of test coverage increases the risk of undetected bugs and regressions
- **Priority Level**: High - Should be addressed early to ensure reliability
- **Implementation Status**: Minimal - Only the main client class has tests
- **Spec Compliance**: Partial - Current tests don't verify compliance with the API specification
- **Difficulty/Complexity**: Medium - Requires understanding of existing codebase and testing patterns, but follows established practices

### Recommended Action
Create comprehensive unit tests for all API service classes using mocks to simulate API responses. Ensure tests cover all public methods and edge cases.

## 2. Add integration tests

### Problem Statement
The library lacks integration tests that verify its behavior against the actual Asana API. This means that while individual components might work correctly in isolation, there's no guarantee they work correctly together or with the real API.

### Code Examples

#### Current Implementation:
```php
// Only unit tests exist, no integration tests
```

#### Expected Implementation:
```php
// In tests/Integration/TaskIntegrationTest.php
class TaskIntegrationTest extends TestCase
{
    private static $client;
    private static $workspace;
    private static $createdTasks = [];

    public static function setUpBeforeClass(): void
    {
        // Use a test PAT from environment variable
        $pat = getenv('ASANA_TEST_PAT');
        if (!$pat) {
            self::markTestSkipped('ASANA_TEST_PAT environment variable not set');
        }

        self::$client = AsanaClient::withPAT($pat);

        // Get a workspace to use for testing
        $workspaces = self::$client->workspaces()->getWorkspaces();
        if (empty($workspaces['data'])) {
            self::markTestSkipped('No workspaces available for testing');
        }

        self::$workspace = $workspaces['data'][0]['gid'];
    }

    public static function tearDownAfterClass(): void
    {
        // Clean up created tasks
        foreach (self::$createdTasks as $taskId) {
            try {
                self::$client->tasks()->deleteTask($taskId);
            } catch (Exception $e) {
                // Log but continue cleanup
                error_log("Failed to delete task {$taskId}: " . $e->getMessage());
            }
        }
    }

    public function testCreateAndGetTask()
    {
        $taskName = 'Integration Test Task ' . uniqid();
        $taskData = [
            'name' => $taskName,
            'workspace' => self::$workspace,
            'notes' => 'Created by integration test'
        ];

        // Create a task
        $createResponse = self::$client->tasks()->createTask($taskData);
        $this->assertArrayHasKey('data', $createResponse);
        $this->assertArrayHasKey('gid', $createResponse['data']);
        $this->assertEquals($taskName, $createResponse['data']['name']);

        $taskId = $createResponse['data']['gid'];
        self::$createdTasks[] = $taskId; // Store for cleanup

        // Get the task
        $getResponse = self::$client->tasks()->getTask($taskId);
        $this->assertArrayHasKey('data', $getResponse);
        $this->assertEquals($taskId, $getResponse['data']['gid']);
        $this->assertEquals($taskName, $getResponse['data']['name']);
    }

    public function testUpdateTask()
    {
        // Create a task for updating
        $taskName = 'Update Test Task ' . uniqid();
        $taskData = [
            'name' => $taskName,
            'workspace' => self::$workspace
        ];

        $createResponse = self::$client->tasks()->createTask($taskData);
        $taskId = $createResponse['data']['gid'];
        self::$createdTasks[] = $taskId; // Store for cleanup

        // Update the task
        $updatedName = 'Updated ' . $taskName;
        $updateResponse = self::$client->tasks()->updateTask($taskId, [
            'name' => $updatedName,
            'notes' => 'Updated by integration test'
        ]);

        $this->assertArrayHasKey('data', $updateResponse);
        $this->assertEquals($updatedName, $updateResponse['data']['name']);

        // Verify the update
        $getResponse = self::$client->tasks()->getTask($taskId);
        $this->assertEquals($updatedName, $getResponse['data']['name']);
    }

    // Additional integration tests...
}
```

### File References
- `tests/Integration/`: New directory for integration tests
- `tests/Integration/TaskIntegrationTest.php`: Integration tests for tasks
- `tests/Integration/ProjectIntegrationTest.php`: Integration tests for projects
- `phpunit.xml`: Configuration for running integration tests separately

### API Spec Validation
Integration tests directly validate compliance with the API specification by testing against the actual API. They verify that:
1. The client can successfully communicate with the API
2. Requests are formatted correctly according to the API specification
3. Responses are processed correctly
4. Error handling works as expected with real API errors

### Critical Evaluation
- **Actual Impact**: High - Without integration tests, there's no guarantee the library works correctly with the actual API
- **Priority Level**: High - Should be addressed to ensure reliability
- **Implementation Status**: Not implemented - No integration tests exist
- **Spec Compliance**: Not validated - Without integration tests, compliance with the API specification isn't verified
- **Difficulty/Complexity**: High - Requires setting up test environments, handling real API calls, managing test data cleanup, and dealing with external dependencies

### Recommended Action
Create a suite of integration tests that verify the library's behavior against the actual Asana API. Use a test account or sandbox environment to avoid affecting production data.
