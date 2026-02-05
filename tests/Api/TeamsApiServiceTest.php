<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\TeamsApiService;
use BrightleafDigital\Http\AsanaApiClient;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;

class TeamsApiServiceTest extends TestCase
{
    /** @var AsanaApiClient&\PHPUnit\Framework\MockObject\MockObject */
    private $mockClient;

    /** @var TeamsApiService */
    private $service;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(AsanaApiClient::class);
        $this->service = new TeamsApiService($this->mockClient);
    }

    /**
     * Test getTeam calls client with correct parameters.
     */
    public function testGetTeam(): void
    {
        $expectedResponse = ['gid' => '12345', 'resource_type' => 'team', 'name' => 'Engineering'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'teams/12345', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getTeam('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getTeam with options.
     */
    public function testGetTeamWithOptions(): void
    {
        $options = ['opt_fields' => 'name,description,organization'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'teams/12345', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTeam('12345', $options);
    }

    /**
     * Test getTeam with custom response type.
     */
    public function testGetTeamWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'teams/12345', ['query' => []], AsanaApiClient::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getTeam('12345', [], AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test getTeam throws exception for empty GID.
     */
    public function testGetTeamThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Team GID must be a non-empty string.');

        $this->service->getTeam('');
    }

    /**
     * Test getTeam throws exception for non-numeric GID.
     */
    public function testGetTeamThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Team GID must be a numeric string.');

        $this->service->getTeam('abc');
    }

    /**
     * Test getTeamsForUser calls client with correct parameters.
     */
    public function testGetTeamsForUser(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'name' => 'Engineering'],
            ['gid' => '222', 'name' => 'Design'],
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'users/12345/teams',
                ['query' => ['organization' => '67890']],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getTeamsForUser('12345', '67890');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getTeamsForUser with options.
     */
    public function testGetTeamsForUserWithOptions(): void
    {
        $options = ['opt_fields' => 'name,description'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'users/12345/teams',
                ['query' => ['opt_fields' => 'name,description', 'organization' => '67890']],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getTeamsForUser('12345', '67890', $options);
    }

    /**
     * Test getTeamsForUser with custom response type.
     */
    public function testGetTeamsForUserWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'users/12345/teams',
                ['query' => ['organization' => '67890']],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getTeamsForUser('12345', '67890', [], AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test getTeamsForUser throws exception for empty user GID.
     */
    public function testGetTeamsForUserThrowsExceptionForEmptyUserGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User GID must be a non-empty string.');

        $this->service->getTeamsForUser('', '67890');
    }

    /**
     * Test getTeamsForUser throws exception for non-numeric user GID.
     */
    public function testGetTeamsForUserThrowsExceptionForNonNumericUserGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User GID must be a numeric string.');

        $this->service->getTeamsForUser('abc', '67890');
    }

    /**
     * Test getTeamsForUser throws exception for empty organization GID.
     */
    public function testGetTeamsForUserThrowsExceptionForEmptyOrgGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Organization GID must be a non-empty string.');

        $this->service->getTeamsForUser('12345', '');
    }

    /**
     * Test getTeamsForUser throws exception for non-numeric organization GID.
     */
    public function testGetTeamsForUserThrowsExceptionForNonNumericOrgGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Organization GID must be a numeric string.');

        $this->service->getTeamsForUser('12345', 'abc');
    }

    /**
     * Test getTeamsForWorkspace calls client with correct parameters.
     */
    public function testGetTeamsForWorkspace(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'name' => 'Engineering'],
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/teams', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getTeamsForWorkspace('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getTeamsForWorkspace with options.
     */
    public function testGetTeamsForWorkspaceWithOptions(): void
    {
        $options = ['opt_fields' => 'name,description', 'limit' => 50];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/teams', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTeamsForWorkspace('12345', $options);
    }

    /**
     * Test getTeamsForWorkspace throws exception for empty GID.
     */
    public function testGetTeamsForWorkspaceThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Workspace GID must be a non-empty string.');

        $this->service->getTeamsForWorkspace('');
    }

    /**
     * Test getTeamsForWorkspace throws exception for non-numeric GID.
     */
    public function testGetTeamsForWorkspaceThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Workspace GID must be a numeric string.');

        $this->service->getTeamsForWorkspace('abc');
    }

    /**
     * Test createTeam calls client with correct parameters.
     */
    public function testCreateTeam(): void
    {
        $data = ['name' => 'Engineering', 'organization' => '12345'];
        $expectedResponse = ['gid' => '99999', 'resource_type' => 'team', 'name' => 'Engineering'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'teams',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->createTeam($data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test createTeam with options.
     */
    public function testCreateTeamWithOptions(): void
    {
        $data = ['name' => 'Engineering', 'organization' => '12345'];
        $options = ['opt_fields' => 'name,description,organization'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'teams',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createTeam($data, $options);
    }

    /**
     * Test createTeam with optional description and visibility.
     */
    public function testCreateTeamWithOptionalFields(): void
    {
        $data = [
            'name' => 'Engineering',
            'organization' => '12345',
            'description' => 'The engineering team',
            'visibility' => 'secret',
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'teams',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createTeam($data);
    }

    /**
     * Test createTeam throws exception when name is missing.
     */
    public function testCreateTeamThrowsExceptionForMissingName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for team creation: name');

        $this->service->createTeam(['organization' => '12345']);
    }

    /**
     * Test createTeam throws exception when organization is missing.
     */
    public function testCreateTeamThrowsExceptionForMissingOrganization(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for team creation: organization');

        $this->service->createTeam(['name' => 'Engineering']);
    }

    /**
     * Test createTeam throws exception when both name and organization are missing.
     */
    public function testCreateTeamThrowsExceptionForMissingBothFields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for team creation: name, organization');

        $this->service->createTeam([]);
    }

    /**
     * Test updateTeam calls client with correct parameters.
     */
    public function testUpdateTeam(): void
    {
        $data = ['name' => 'Updated Team', 'description' => 'New description'];
        $expectedResponse = ['gid' => '12345', 'resource_type' => 'team', 'name' => 'Updated Team'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'teams/12345',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->updateTeam('12345', $data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test updateTeam with options.
     */
    public function testUpdateTeamWithOptions(): void
    {
        $data = ['name' => 'Updated Team'];
        $options = ['opt_fields' => 'name,description'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'teams/12345',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateTeam('12345', $data, $options);
    }

    /**
     * Test updateTeam with custom response type.
     */
    public function testUpdateTeamWithCustomResponseType(): void
    {
        $data = ['name' => 'Updated Team'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'teams/12345',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->updateTeam('12345', $data, [], AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test updateTeam throws exception for empty GID.
     */
    public function testUpdateTeamThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Team GID must be a non-empty string.');

        $this->service->updateTeam('', ['name' => 'Test']);
    }

    /**
     * Test updateTeam throws exception for non-numeric GID.
     */
    public function testUpdateTeamThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Team GID must be a numeric string.');

        $this->service->updateTeam('abc', ['name' => 'Test']);
    }

    /**
     * Test addUserToTeam calls client with correct parameters.
     */
    public function testAddUserToTeam(): void
    {
        $data = ['user' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'teams/12345/addUser',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addUserToTeam('12345', $data);
    }

    /**
     * Test addUserToTeam with options.
     */
    public function testAddUserToTeamWithOptions(): void
    {
        $data = ['user' => '67890'];
        $options = ['opt_fields' => 'user,team'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'teams/12345/addUser',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addUserToTeam('12345', $data, $options);
    }

    /**
     * Test addUserToTeam throws exception for empty team GID.
     */
    public function testAddUserToTeamThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Team GID must be a non-empty string.');

        $this->service->addUserToTeam('', ['user' => '67890']);
    }

    /**
     * Test addUserToTeam throws exception for non-numeric team GID.
     */
    public function testAddUserToTeamThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Team GID must be a numeric string.');

        $this->service->addUserToTeam('abc', ['user' => '67890']);
    }

    /**
     * Test addUserToTeam throws exception when user field is missing.
     */
    public function testAddUserToTeamThrowsExceptionForMissingUser(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for adding user to team: user');

        $this->service->addUserToTeam('12345', []);
    }

    /**
     * Test removeUserFromTeam calls client with correct parameters.
     */
    public function testRemoveUserFromTeam(): void
    {
        $data = ['user' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'teams/12345/removeUser',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $result = $this->service->removeUserFromTeam('12345', $data);

        $this->assertSame([], $result);
    }

    /**
     * Test removeUserFromTeam with options.
     */
    public function testRemoveUserFromTeamWithOptions(): void
    {
        $data = ['user' => '67890'];
        $options = ['opt_pretty' => true];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'teams/12345/removeUser',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->removeUserFromTeam('12345', $data, $options);
    }

    /**
     * Test removeUserFromTeam throws exception for empty team GID.
     */
    public function testRemoveUserFromTeamThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Team GID must be a non-empty string.');

        $this->service->removeUserFromTeam('', ['user' => '67890']);
    }

    /**
     * Test removeUserFromTeam throws exception for non-numeric team GID.
     */
    public function testRemoveUserFromTeamThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Team GID must be a numeric string.');

        $this->service->removeUserFromTeam('abc', ['user' => '67890']);
    }

    /**
     * Test removeUserFromTeam throws exception when user field is missing.
     */
    public function testRemoveUserFromTeamThrowsExceptionForMissingUser(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for removing user from team: user');

        $this->service->removeUserFromTeam('12345', []);
    }
}
