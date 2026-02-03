<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\UserApiService;
use BrightleafDigital\Http\AsanaApiClient;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;

class UserApiServiceTest extends TestCase
{
    /** @var AsanaApiClient&\PHPUnit\Framework\MockObject\MockObject */
    private $mockClient;

    /** @var UserApiService */
    private $service;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(AsanaApiClient::class);
        $this->service = new UserApiService($this->mockClient);
    }

    /**
     * Test getUsers with workspace parameter.
     */
    public function testGetUsersWithWorkspace(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'users', ['query' => ['workspace' => '12345']], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getUsers('12345');
    }

    /**
     * Test getUsers with team parameter.
     */
    public function testGetUsersWithTeam(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'users', ['query' => ['team' => '67890']], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getUsers(null, '67890');
    }

    /**
     * Test getUsers throws exception without workspace or team.
     */
    public function testGetUsersThrowsExceptionWithoutWorkspaceOrTeam(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must provide either a "workspace" or "team".');

        $this->service->getUsers(null, null);
    }

    /**
     * Test getUsers with options.
     */
    public function testGetUsersWithOptions(): void
    {
        $options = ['opt_fields' => 'name,email', 'limit' => 50];
        $expectedQuery = array_merge(['workspace' => '12345'], $options);

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'users', ['query' => $expectedQuery], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getUsers('12345', null, $options);
    }

    /**
     * Test getUser calls client with correct parameters.
     */
    public function testGetUser(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'users/12345', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getUser('12345');
    }

    /**
     * Test getUser with 'me' identifier.
     */
    public function testGetUserWithMeIdentifier(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'users/me', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getUser('me');
    }

    /**
     * Test getUserFavorites calls client with correct parameters.
     */
    public function testGetUserFavorites(): void
    {
        $options = ['workspace' => '12345', 'resource_type' => 'project'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'users/67890/favorites', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getUserFavorites('67890', $options);
    }

    /**
     * Test getUsersForTeam calls client with correct parameters.
     */
    public function testGetUsersForTeam(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'teams/12345/users', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getUsersForTeam('12345');
    }

    /**
     * Test getUsersForWorkspace calls client with correct parameters.
     */
    public function testGetUsersForWorkspace(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/users', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getUsersForWorkspace('12345');
    }

    /**
     * Test getCurrentUser calls getUser with 'me'.
     */
    public function testGetCurrentUser(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'users/me', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getCurrentUser();
    }

    /**
     * Test getCurrentUser with options.
     */
    public function testGetCurrentUserWithOptions(): void
    {
        $options = ['opt_fields' => 'name,email,photo'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'users/me', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getCurrentUser($options);
    }

    /**
     * Test getCurrentUserFavorites calls getUserFavorites with 'me'.
     */
    public function testGetCurrentUserFavorites(): void
    {
        $options = ['workspace' => '12345', 'resource_type' => 'project'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'users/me/favorites', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getCurrentUserFavorites($options);
    }

    /**
     * Test getUser with custom response type.
     */
    public function testGetUserWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'users/12345', ['query' => []], AsanaApiClient::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getUser('12345', [], AsanaApiClient::RESPONSE_FULL);
    }
}
