<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\ProjectApiService;
use BrightleafDigital\Http\AsanaApiClient;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;

class ProjectApiServiceTest extends TestCase
{
    private AsanaApiClient $mockClient;
    private ProjectApiService $service;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(AsanaApiClient::class);
        $this->service = new ProjectApiService($this->mockClient);
    }

    /**
     * Test getProjects with workspace parameter.
     */
    public function testGetProjectsWithWorkspace(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'projects', ['query' => ['workspace' => '12345']], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getProjects('12345');
    }

    /**
     * Test getProjects with team parameter.
     */
    public function testGetProjectsWithTeam(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'projects', ['query' => ['team' => '67890']], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getProjects(null, '67890');
    }

    /**
     * Test getProjects with both workspace and team.
     */
    public function testGetProjectsWithWorkspaceAndTeam(): void
    {
        $expectedQuery = ['workspace' => '12345', 'team' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'projects', ['query' => $expectedQuery], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getProjects('12345', '67890');
    }

    /**
     * Test getProjects throws exception without workspace or team.
     */
    public function testGetProjectsThrowsExceptionWithoutWorkspaceOrTeam(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must provide either a "workspace" or "team" parameter.');

        $this->service->getProjects(null, null);
    }

    /**
     * Test getProjects with options.
     */
    public function testGetProjectsWithOptions(): void
    {
        $options = ['archived' => false, 'limit' => 50, 'opt_fields' => 'name,owner'];
        $expectedQuery = array_merge(['workspace' => '12345'], $options);

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'projects', ['query' => $expectedQuery], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getProjects('12345', null, $options);
    }

    /**
     * Test createProject calls client with correct parameters.
     */
    public function testCreateProject(): void
    {
        $projectData = ['name' => 'New Project', 'workspace' => '12345'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'projects',
                ['json' => ['data' => $projectData], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createProject($projectData);
    }

    /**
     * Test getProject calls client with correct parameters.
     */
    public function testGetProject(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'projects/12345', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getProject('12345');
    }

    /**
     * Test updateProject calls client with correct parameters.
     */
    public function testUpdateProject(): void
    {
        $updateData = ['name' => 'Updated Project'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'projects/12345',
                ['json' => ['data' => $updateData], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateProject('12345', $updateData);
    }

    /**
     * Test deleteProject calls client with correct parameters.
     */
    public function testDeleteProject(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'projects/12345', [], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->deleteProject('12345');
    }

    /**
     * Test duplicateProject calls client with correct parameters.
     */
    public function testDuplicateProject(): void
    {
        $data = ['name' => 'Duplicated Project', 'include' => ['members', 'notes']];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'projects/12345/duplicate',
                ['json' => ['data' => $data]],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->duplicateProject('12345', $data);
    }

    /**
     * Test getProjectsForTask calls client with correct parameters.
     */
    public function testGetProjectsForTask(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tasks/67890/projects', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getProjectsForTask('67890');
    }

    /**
     * Test getProjectsForTeam calls client with correct parameters.
     */
    public function testGetProjectsForTeam(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'teams/99999/projects', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getProjectsForTeam('99999');
    }

    /**
     * Test createProjectInTeam calls client with correct parameters.
     */
    public function testCreateProjectInTeam(): void
    {
        $data = ['name' => 'Team Project'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'teams/99999/projects',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createProjectInTeam('99999', $data);
    }

    /**
     * Test getProjectsForWorkspace calls client with correct parameters.
     */
    public function testGetProjectsForWorkspace(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/projects', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getProjectsForWorkspace('12345');
    }

    /**
     * Test createProjectInWorkspace calls client with correct parameters.
     */
    public function testCreateProjectInWorkspace(): void
    {
        $data = ['name' => 'Workspace Project'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'workspaces/12345/projects',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createProjectInWorkspace('12345', $data);
    }

    /**
     * Test addCustomFieldToProject calls client with correct parameters.
     */
    public function testAddCustomFieldToProject(): void
    {
        $data = ['custom_field' => '55555', 'is_important' => true];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'projects/12345/addCustomFieldSetting',
                ['json' => ['data' => $data]],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addCustomFieldToProject('12345', $data);
    }

    /**
     * Test removeCustomFieldFromProject calls client with correct parameters.
     */
    public function testRemoveCustomFieldFromProject(): void
    {
        $data = ['custom_field' => '55555'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'projects/12345/removeCustomFieldSetting',
                ['json' => ['data' => $data]],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->removeCustomFieldFromProject('12345', $data);
    }

    /**
     * Test getCustomFieldsForProject calls client with correct parameters.
     */
    public function testGetCustomFieldsForProject(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'projects/12345/custom_field_settings', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getCustomFieldsForProject('12345');
    }

    /**
     * Test getTaskCountsForProject calls client with correct parameters.
     */
    public function testGetTaskCountsForProject(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'projects/12345/task_counts', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTaskCountsForProject('12345');
    }

    /**
     * Test addMembersToProject calls client with correct parameters.
     */
    public function testAddMembersToProject(): void
    {
        $members = ['user1', 'user2'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'projects/12345/addMembers',
                ['json' => ['data' => ['members' => $members]], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addMembersToProject('12345', $members);
    }

    /**
     * Test removeMembersFromProject calls client with correct parameters.
     */
    public function testRemoveMembersFromProject(): void
    {
        $members = ['user1'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'projects/12345/removeMembers',
                ['json' => ['data' => ['members' => $members]], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->removeMembersFromProject('12345', $members);
    }

    /**
     * Test addFollowersToProject calls client with correct parameters.
     */
    public function testAddFollowersToProject(): void
    {
        $followers = ['user1', 'user2'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'projects/12345/addFollowers',
                ['json' => ['data' => ['followers' => $followers]], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addFollowersToProject('12345', $followers);
    }

    /**
     * Test removeFollowersFromProject calls client with correct parameters.
     */
    public function testRemoveFollowersFromProject(): void
    {
        $followers = ['user1'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'projects/12345/removeFollowers',
                ['json' => ['data' => ['followers' => $followers]], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->removeFollowersFromProject('12345', $followers);
    }

    /**
     * Test createProjectTemplateFromProject calls client with correct parameters.
     */
    public function testCreateProjectTemplateFromProject(): void
    {
        $data = ['name' => 'Template Name', 'description' => 'Template description'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'projects/12345/saveAsTemplate',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createProjectTemplateFromProject('12345', $data);
    }
}
