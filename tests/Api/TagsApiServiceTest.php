<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\TagsApiService;
use BrightleafDigital\Http\AsanaApiClient;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;

class TagsApiServiceTest extends TestCase
{
    /** @var AsanaApiClient&\PHPUnit\Framework\MockObject\MockObject */
    private $mockClient;

    /** @var TagsApiService */
    private $service;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(AsanaApiClient::class);
        $this->service = new TagsApiService($this->mockClient);
    }

    /**
     * Test getTags calls client with correct parameters.
     */
    public function testGetTags(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tags', ['query' => ['workspace' => '12345']], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTags('12345');
    }

    /**
     * Test getTags with options.
     */
    public function testGetTagsWithOptions(): void
    {
        $options = ['opt_fields' => 'name,color', 'limit' => 50];
        $expectedQuery = array_merge(['workspace' => '12345'], $options);

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tags', ['query' => $expectedQuery], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTags('12345', $options);
    }

    /**
     * Test createTag calls client with correct parameters.
     */
    public function testCreateTag(): void
    {
        $data = ['name' => 'New Tag', 'workspace' => '12345'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tags',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createTag($data);
    }

    /**
     * Test createTag with color.
     */
    public function testCreateTagWithColor(): void
    {
        $data = ['name' => 'Priority', 'workspace' => '12345', 'color' => 'dark-red'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tags',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createTag($data);
    }

    /**
     * Test getTag calls client with correct parameters.
     */
    public function testGetTag(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tags/12345', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTag('12345');
    }

    /**
     * Test getTag with options.
     */
    public function testGetTagWithOptions(): void
    {
        $options = ['opt_fields' => 'name,color,notes'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tags/12345', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTag('12345', $options);
    }

    /**
     * Test updateTag calls client with correct parameters.
     */
    public function testUpdateTag(): void
    {
        $data = ['name' => 'Updated Tag', 'color' => 'light-green'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'tags/12345',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateTag('12345', $data);
    }

    /**
     * Test deleteTag calls client with correct parameters.
     */
    public function testDeleteTag(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'tags/12345', [], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->deleteTag('12345');
    }

    /**
     * Test getTasksForTag calls client with correct parameters.
     */
    public function testGetTasksForTag(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tags/12345/tasks', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTasksForTag('12345');
    }

    /**
     * Test getTasksForTag with options.
     */
    public function testGetTasksForTagWithOptions(): void
    {
        $options = ['opt_fields' => 'name,assignee,completed', 'limit' => 100];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tags/12345/tasks', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTasksForTag('12345', $options);
    }

    /**
     * Test getTagsForWorkspace calls client with correct parameters.
     */
    public function testGetTagsForWorkspace(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/tags', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTagsForWorkspace('12345');
    }

    /**
     * Test createTagInWorkspace calls client with correct parameters.
     */
    public function testCreateTagInWorkspace(): void
    {
        $data = ['name' => 'Workspace Tag', 'color' => 'dark-blue'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'workspaces/12345/tags',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createTagInWorkspace('12345', $data);
    }

    /**
     * Test methods with custom response type.
     */
    public function testGetTagWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tags/12345', ['query' => []], AsanaApiClient::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getTag('12345', [], AsanaApiClient::RESPONSE_FULL);
    }
}
