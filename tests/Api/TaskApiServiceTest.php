<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\TaskApiService;
use BrightleafDigital\Http\AsanaApiClient;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;

class TaskApiServiceTest extends TestCase
{
    /** @var AsanaApiClient&\PHPUnit\Framework\MockObject\MockObject */
    private $mockClient;

    /** @var TaskApiService */
    private $service;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(AsanaApiClient::class);
        $this->service = new TaskApiService($this->mockClient);
    }

    /**
     * Test getTasks calls client with correct parameters.
     */
    public function testGetTasks(): void
    {
        $options = ['workspace' => '12345', 'assignee' => 'me'];
        $expectedResponse = [['gid' => '111', 'name' => 'Task 1'], ['gid' => '222', 'name' => 'Task 2']];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tasks', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getTasks($options);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getTasks with custom response type.
     */
    public function testGetTasksWithCustomResponseType(): void
    {
        $options = ['project' => '12345'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tasks', ['query' => $options], AsanaApiClient::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getTasks($options, AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test createTask calls client with correct parameters.
     */
    public function testCreateTask(): void
    {
        $taskData = ['name' => 'New Task', 'workspace' => '12345'];
        $expectedResponse = ['gid' => '99999', 'name' => 'New Task'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks',
                ['json' => ['data' => $taskData], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->createTask($taskData);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test createTask with options.
     */
    public function testCreateTaskWithOptions(): void
    {
        $taskData = ['name' => 'New Task', 'workspace' => '12345'];
        $options = ['opt_fields' => 'name,assignee,completed'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks',
                ['json' => ['data' => $taskData], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createTask($taskData, $options);
    }

    /**
     * Test getTask calls client with correct parameters.
     */
    public function testGetTask(): void
    {
        $taskGid = '12345';
        $expectedResponse = ['gid' => '12345', 'name' => 'Test Task', 'completed' => false];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tasks/12345', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getTask($taskGid);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getTask with options.
     */
    public function testGetTaskWithOptions(): void
    {
        $options = ['opt_fields' => 'name,assignee.name,projects.name'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tasks/12345', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTask('12345', $options);
    }

    /**
     * Test updateTask calls client with correct parameters.
     */
    public function testUpdateTask(): void
    {
        $taskGid = '12345';
        $updateData = ['name' => 'Updated Task', 'completed' => true];
        $expectedResponse = ['gid' => '12345', 'name' => 'Updated Task', 'completed' => true];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'tasks/12345',
                ['json' => ['data' => $updateData], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->updateTask($taskGid, $updateData);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test deleteTask calls client with correct parameters.
     */
    public function testDeleteTask(): void
    {
        $taskGid = '12345';

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'tasks/12345', [], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $result = $this->service->deleteTask($taskGid);

        $this->assertSame([], $result);
    }

    /**
     * Test getSubtasksFromTask calls client with correct parameters.
     */
    public function testGetSubtasksFromTask(): void
    {
        $taskGid = '12345';
        $expectedResponse = [['gid' => '111', 'name' => 'Subtask 1']];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tasks/12345/subtasks', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getSubtasksFromTask($taskGid);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test createSubtaskForTask calls client with correct parameters.
     */
    public function testCreateSubtaskForTask(): void
    {
        $taskGid = '12345';
        $subtaskData = ['name' => 'New Subtask'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks/12345/subtasks',
                ['json' => ['data' => $subtaskData], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createSubtaskForTask($taskGid, $subtaskData);
    }

    /**
     * Test getTasksByProject calls client with correct parameters.
     */
    public function testGetTasksByProject(): void
    {
        $projectGid = '67890';
        $expectedResponse = [['gid' => '111', 'name' => 'Project Task']];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'projects/67890/tasks', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getTasksByProject($projectGid);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getTasksBySection calls client with correct parameters.
     */
    public function testGetTasksBySection(): void
    {
        $sectionGid = '99999';

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'sections/99999/tasks', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTasksBySection($sectionGid);
    }

    /**
     * Test getTasksByTag calls client with correct parameters.
     */
    public function testGetTasksByTag(): void
    {
        $tagGid = '55555';

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tags/55555/tasks', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTasksByTag($tagGid);
    }

    /**
     * Test addProjectToTask calls client with correct parameters.
     */
    public function testAddProjectToTask(): void
    {
        $taskGid = '12345';
        $projectGid = '67890';

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks/12345/addProject',
                ['json' => ['data' => ['project' => $projectGid]]],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addProjectToTask($taskGid, $projectGid);
    }

    /**
     * Test removeProjectFromTask calls client with correct parameters.
     */
    public function testRemoveProjectFromTask(): void
    {
        $taskGid = '12345';
        $projectGid = '67890';

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks/12345/removeProject',
                ['json' => ['data' => ['project' => $projectGid]]],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->removeProjectFromTask($taskGid, $projectGid);
    }

    /**
     * Test addTagToTask calls client with correct parameters.
     */
    public function testAddTagToTask(): void
    {
        $taskGid = '12345';
        $tagGid = '55555';

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks/12345/addTag',
                ['json' => ['data' => ['tag' => $tagGid]]],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addTagToTask($taskGid, $tagGid);
    }

    /**
     * Test removeTagFromTask calls client with correct parameters.
     */
    public function testRemoveTagFromTask(): void
    {
        $taskGid = '12345';
        $tagGid = '55555';

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks/12345/removeTag',
                ['json' => ['data' => ['tag' => $tagGid]]],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->removeTagFromTask($taskGid, $tagGid);
    }

    /**
     * Test setParentForTask calls client with correct parameters.
     */
    public function testSetParentForTask(): void
    {
        $taskGid = '12345';
        $data = ['parent' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks/12345/setParent',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->setParentForTask($taskGid, $data);
    }

    /**
     * Test addFollowersToTask calls client with correct parameters.
     */
    public function testAddFollowersToTask(): void
    {
        $taskGid = '12345';
        $followers = ['user1', 'user2'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks/12345/addFollowers',
                ['json' => ['data' => ['followers' => $followers]], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addFollowersToTask($taskGid, $followers);
    }

    /**
     * Test removeFollowersFromTask calls client with correct parameters.
     */
    public function testRemoveFollowersFromTask(): void
    {
        $taskGid = '12345';
        $followers = ['user1'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks/12345/removeFollowers',
                ['json' => ['data' => ['followers' => $followers]], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->removeFollowersFromTask($taskGid, $followers);
    }

    /**
     * Test duplicateTask calls client with correct parameters.
     */
    public function testDuplicateTask(): void
    {
        $taskGid = '12345';
        $data = ['name' => 'Duplicated Task', 'include' => ['assignee', 'notes']];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks/12345/duplicate',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->duplicateTask($taskGid, $data);
    }

    /**
     * Test getDependenciesFromTask calls client with correct parameters.
     */
    public function testGetDependenciesFromTask(): void
    {
        $taskGid = '12345';

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tasks/12345/dependencies', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getDependenciesFromTask($taskGid);
    }

    /**
     * Test setDependenciesForTask calls client with correct parameters.
     */
    public function testSetDependenciesForTask(): void
    {
        $taskGid = '12345';
        $data = ['dependencies' => ['task1', 'task2']];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks/12345/addDependencies',
                ['json' => ['data' => $data]],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->setDependenciesForTask($taskGid, $data);
    }

    /**
     * Test unlinkDependenciesFromTask calls client with correct parameters.
     */
    public function testUnlinkDependenciesFromTask(): void
    {
        $taskGid = '12345';
        $data = ['dependencies' => ['task1']];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks/12345/removeDependencies',
                ['json' => ['data' => $data]],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->unlinkDependenciesFromTask($taskGid, $data);
    }

    /**
     * Test getDependentsFromTask calls client with correct parameters.
     */
    public function testGetDependentsFromTask(): void
    {
        $taskGid = '12345';

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tasks/12345/dependents', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getDependentsFromTask($taskGid);
    }

    /**
     * Test setDependentsForTask calls client with correct parameters.
     */
    public function testSetDependentsForTask(): void
    {
        $taskGid = '12345';
        $data = ['dependents' => ['task3', 'task4']];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks/12345/addDependents',
                ['json' => ['data' => $data]],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->setDependentsForTask($taskGid, $data);
    }

    /**
     * Test unlinkDependentsFromTask calls client with correct parameters.
     */
    public function testUnlinkDependentsFromTask(): void
    {
        $taskGid = '12345';
        $data = ['dependents' => ['task3']];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks/12345/removeDependents',
                ['json' => ['data' => $data]],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->unlinkDependentsFromTask($taskGid, $data);
    }
}
