<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\UserTaskListsApiService;
use BrightleafDigital\Http\AsanaApiClient;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;

class UserTaskListsApiServiceTest extends TestCase
{
    /** @var AsanaApiClient&\PHPUnit\Framework\MockObject\MockObject */
    private $mockClient;

    /** @var UserTaskListsApiService */
    private $service;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(AsanaApiClient::class);
        $this->service = new UserTaskListsApiService($this->mockClient);
    }

    // ── getUserTaskList ─────────────────────────────────────────────────

    /**
     * Test getUserTaskList calls client with correct parameters.
     */
    public function testGetUserTaskList(): void
    {
        $expectedResponse = [
            'gid' => '12345',
            'resource_type' => 'user_task_list',
            'name' => 'My Tasks',
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'user_task_lists/12345',
                ['query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getUserTaskList('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getUserTaskList with options.
     */
    public function testGetUserTaskListWithOptions(): void
    {
        $options = ['opt_fields' => 'name,owner,workspace'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'user_task_lists/12345',
                ['query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getUserTaskList('12345', $options);
    }

    /**
     * Test getUserTaskList with custom response type.
     */
    public function testGetUserTaskListWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'user_task_lists/12345',
                ['query' => []],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getUserTaskList(
            '12345',
            [],
            AsanaApiClient::RESPONSE_FULL
        );
    }

    /**
     * Test getUserTaskList throws exception for empty GID.
     */
    public function testGetUserTaskListThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'User Task List GID must be a non-empty string.'
        );

        $this->service->getUserTaskList('');
    }

    /**
     * Test getUserTaskList throws exception for non-numeric GID.
     */
    public function testGetUserTaskListThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'User Task List GID must be a numeric string.'
        );

        $this->service->getUserTaskList('abc');
    }

    // ── getUserTaskListForUser ───────────────────────────────────────────

    /**
     * Test getUserTaskListForUser calls client with correct parameters.
     */
    public function testGetUserTaskListForUser(): void
    {
        $expectedResponse = [
            'gid' => '99999',
            'resource_type' => 'user_task_list',
            'name' => 'My Tasks',
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'users/12345/user_task_list',
                ['query' => ['workspace' => '67890']],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getUserTaskListForUser('12345', '67890');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getUserTaskListForUser with options.
     */
    public function testGetUserTaskListForUserWithOptions(): void
    {
        $options = ['opt_fields' => 'name,owner,workspace'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'users/12345/user_task_list',
                ['query' => [
                    'opt_fields' => 'name,owner,workspace',
                    'workspace' => '67890',
                ]],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getUserTaskListForUser('12345', '67890', $options);
    }

    /**
     * Test getUserTaskListForUser with custom response type.
     */
    public function testGetUserTaskListForUserWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'users/12345/user_task_list',
                ['query' => ['workspace' => '67890']],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getUserTaskListForUser(
            '12345',
            '67890',
            [],
            AsanaApiClient::RESPONSE_FULL
        );
    }

    /**
     * Test getUserTaskListForUser throws exception for empty user GID.
     */
    public function testGetUserTaskListForUserThrowsExceptionForEmptyUserGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'User GID must be a non-empty string.'
        );

        $this->service->getUserTaskListForUser('', '67890');
    }

    /**
     * Test getUserTaskListForUser throws exception for non-numeric user GID.
     */
    public function testGetUserTaskListForUserThrowsExceptionForNonNumericUserGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'User GID must be a numeric string.'
        );

        $this->service->getUserTaskListForUser('abc', '67890');
    }

    /**
     * Test getUserTaskListForUser throws exception for empty workspace GID.
     */
    public function testGetUserTaskListForUserThrowsExceptionForEmptyWorkspaceGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Workspace GID must be a non-empty string.'
        );

        $this->service->getUserTaskListForUser('12345', '');
    }

    /**
     * Test getUserTaskListForUser throws exception for non-numeric workspace GID.
     */
    public function testGetUserTaskListForUserThrowsExceptionForNonNumericWorkspaceGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Workspace GID must be a numeric string.'
        );

        $this->service->getUserTaskListForUser('12345', 'abc');
    }
}
