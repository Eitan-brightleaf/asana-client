<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\WorkspaceApiService;
use BrightleafDigital\Http\AsanaApiClient;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;

class WorkspaceApiServiceTest extends TestCase
{
    /** @var AsanaApiClient&\PHPUnit\Framework\MockObject\MockObject */
    private $mockClient;

    /** @var WorkspaceApiService */
    private $service;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(AsanaApiClient::class);
        $this->service = new WorkspaceApiService($this->mockClient);
    }

    /**
     * Test getWorkspaces calls client with correct parameters.
     */
    public function testGetWorkspaces(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getWorkspaces();
    }

    /**
     * Test getWorkspaces with options.
     */
    public function testGetWorkspacesWithOptions(): void
    {
        $options = ['opt_fields' => 'name,is_organization', 'limit' => 50];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getWorkspaces($options);
    }

    /**
     * Test getWorkspace calls client with correct parameters.
     */
    public function testGetWorkspace(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getWorkspace('12345');
    }

    /**
     * Test updateWorkspace calls client with correct parameters.
     */
    public function testUpdateWorkspace(): void
    {
        $data = ['name' => 'Updated Workspace'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'workspaces/12345',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateWorkspace('12345', $data);
    }

    /**
     * Test addUserToWorkspace calls client with correct parameters.
     */
    public function testAddUserToWorkspace(): void
    {
        $data = ['user' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'workspaces/12345/addUser',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addUserToWorkspace('12345', $data);
    }

    /**
     * Test addUserToWorkspace with email.
     */
    public function testAddUserToWorkspaceWithEmail(): void
    {
        $data = ['email' => 'user@example.com'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'workspaces/12345/addUser',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addUserToWorkspace('12345', $data);
    }

    /**
     * Test removeUserFromWorkspace calls client with correct parameters.
     */
    public function testRemoveUserFromWorkspace(): void
    {
        $data = ['user' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'workspaces/12345/removeUser',
                ['json' => ['data' => $data]],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->removeUserFromWorkspace('12345', $data);
    }

    /**
     * Test getUsersInWorkspace calls client with correct parameters.
     */
    public function testGetUsersInWorkspace(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/users', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getUsersInWorkspace('12345');
    }

    /**
     * Test getTeamsInWorkspace calls client with correct parameters.
     */
    public function testGetTeamsInWorkspace(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/teams', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTeamsInWorkspace('12345');
    }

    /**
     * Test getProjectsInWorkspace calls client with correct parameters.
     */
    public function testGetProjectsInWorkspace(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/projects', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getProjectsInWorkspace('12345');
    }

    /**
     * Test searchTasksInWorkspace calls client with correct parameters.
     */
    public function testSearchTasksInWorkspace(): void
    {
        $options = ['text' => 'search query', 'completed' => false];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/tasks/search', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->searchTasksInWorkspace('12345', $options);
    }

    /**
     * Test searchTasksInWorkspace with complex query.
     */
    public function testSearchTasksInWorkspaceWithComplexQuery(): void
    {
        $options = [
            'text' => 'urgent',
            'assignee.any' => ['user1', 'user2'],
            'projects.any' => ['project1'],
            'completed' => false,
            'due_on.before' => '2024-12-31',
            'opt_fields' => 'name,assignee,completed'
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/tasks/search', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->searchTasksInWorkspace('12345', $options);
    }

    /**
     * Test getWorkspaceEvents calls client with correct parameters.
     */
    public function testGetWorkspaceEvents(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/events', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getWorkspaceEvents('12345');
    }

    /**
     * Test getWorkspaceEvents with sync token.
     */
    public function testGetWorkspaceEventsWithSyncToken(): void
    {
        $options = ['sync' => 'sync-token-123'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/events', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getWorkspaceEvents('12345', $options);
    }

    /**
     * Test methods with custom response type.
     */
    public function testGetWorkspaceWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345', ['query' => []], AsanaApiClient::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getWorkspace('12345', [], AsanaApiClient::RESPONSE_FULL);
    }
}
