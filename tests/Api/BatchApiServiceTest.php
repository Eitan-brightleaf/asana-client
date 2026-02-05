<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\BatchApiService;
use BrightleafDigital\Http\AsanaApiClient;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;

class BatchApiServiceTest extends TestCase
{
    /** @var AsanaApiClient&\PHPUnit\Framework\MockObject\MockObject */
    private $mockClient;

    /** @var BatchApiService */
    private $service;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(AsanaApiClient::class);
        $this->service = new BatchApiService($this->mockClient);
    }

    // ── submitBatchRequest ──────────────────────────────────────────────

    /**
     * Test submitBatchRequest calls client with correct parameters.
     */
    public function testSubmitBatchRequest(): void
    {
        $actions = [
            [
                'relative_path' => '/tasks/12345',
                'method' => 'get',
            ],
        ];

        $expectedResponse = [
            [
                'status_code' => 200,
                'headers' => [],
                'body' => ['data' => ['gid' => '12345', 'name' => 'Test']],
            ],
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'batch',
                [
                    'json' => ['data' => ['actions' => $actions]],
                    'query' => [],
                ],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->submitBatchRequest($actions);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test submitBatchRequest with multiple actions.
     */
    public function testSubmitBatchRequestWithMultipleActions(): void
    {
        $actions = [
            [
                'relative_path' => '/tasks/12345',
                'method' => 'get',
            ],
            [
                'relative_path' => '/tasks',
                'method' => 'post',
                'data' => ['name' => 'New Task', 'workspace' => '67890'],
            ],
            [
                'relative_path' => '/tasks/11111',
                'method' => 'put',
                'data' => ['name' => 'Updated Task'],
            ],
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'batch',
                [
                    'json' => ['data' => ['actions' => $actions]],
                    'query' => [],
                ],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->submitBatchRequest($actions);
    }

    /**
     * Test submitBatchRequest with options.
     */
    public function testSubmitBatchRequestWithOptions(): void
    {
        $actions = [
            [
                'relative_path' => '/tasks/12345',
                'method' => 'get',
            ],
        ];
        $options = ['opt_fields' => 'status_code,body'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'batch',
                [
                    'json' => ['data' => ['actions' => $actions]],
                    'query' => $options,
                ],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->submitBatchRequest($actions, $options);
    }

    /**
     * Test submitBatchRequest with custom response type.
     */
    public function testSubmitBatchRequestWithCustomResponseType(): void
    {
        $actions = [
            [
                'relative_path' => '/tasks/12345',
                'method' => 'get',
            ],
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'batch',
                [
                    'json' => ['data' => ['actions' => $actions]],
                    'query' => [],
                ],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->submitBatchRequest(
            $actions,
            [],
            AsanaApiClient::RESPONSE_FULL
        );
    }

    /**
     * Test submitBatchRequest with action containing options.
     */
    public function testSubmitBatchRequestWithActionOptions(): void
    {
        $actions = [
            [
                'relative_path' => '/tasks/12345',
                'method' => 'get',
                'options' => [
                    'fields' => ['name', 'assignee'],
                ],
            ],
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'batch',
                [
                    'json' => ['data' => ['actions' => $actions]],
                    'query' => [],
                ],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->submitBatchRequest($actions);
    }

    /**
     * Test submitBatchRequest with delete action.
     */
    public function testSubmitBatchRequestWithDeleteAction(): void
    {
        $actions = [
            [
                'relative_path' => '/tasks/12345',
                'method' => 'delete',
            ],
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'batch',
                [
                    'json' => ['data' => ['actions' => $actions]],
                    'query' => [],
                ],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->submitBatchRequest($actions);
    }

    // ── Validation ──────────────────────────────────────────────────────

    /**
     * Test submitBatchRequest throws exception for empty actions array.
     */
    public function testSubmitBatchRequestThrowsExceptionForEmptyActions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Actions array must not be empty for batch request.'
        );

        $this->service->submitBatchRequest([]);
    }

    /**
     * Test throws exception for action missing relative_path.
     */
    public function testSubmitBatchRequestThrowsExceptionForMissingRelativePath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Action at index 0 is missing required field(s): relative_path'
        );

        $this->service->submitBatchRequest([
            ['method' => 'get'],
        ]);
    }

    /**
     * Test throws exception for action missing method.
     */
    public function testSubmitBatchRequestThrowsExceptionForMissingMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Action at index 0 is missing required field(s): method'
        );

        $this->service->submitBatchRequest([
            ['relative_path' => '/tasks/12345'],
        ]);
    }

    /**
     * Test throws exception for action missing both required fields.
     */
    public function testSubmitBatchRequestThrowsExceptionForMissingBothFields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Action at index 0 is missing required field(s): '
            . 'relative_path, method'
        );

        $this->service->submitBatchRequest([
            ['data' => ['name' => 'test']],
        ]);
    }

    /**
     * Test throws exception for invalid action at specific index.
     */
    public function testSubmitBatchRequestThrowsExceptionForInvalidActionAtIndex(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Action at index 1 is missing required field(s): method'
        );

        $this->service->submitBatchRequest([
            ['relative_path' => '/tasks/12345', 'method' => 'get'],
            ['relative_path' => '/tasks/67890'],
        ]);
    }

    /**
     * Test throws exception for non-array action.
     */
    public function testSubmitBatchRequestThrowsExceptionForNonArrayAction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Each action must be an array, invalid action at index 0.'
        );

        $this->service->submitBatchRequest(['not-an-array']);
    }

    /**
     * Test throws exception for empty relative_path string.
     */
    public function testSubmitBatchRequestThrowsExceptionForEmptyRelativePath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Action at index 0 is missing required field(s): relative_path'
        );

        $this->service->submitBatchRequest([
            ['relative_path' => '', 'method' => 'get'],
        ]);
    }

    /**
     * Test throws exception for empty method string.
     */
    public function testSubmitBatchRequestThrowsExceptionForEmptyMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Action at index 0 is missing required field(s): method'
        );

        $this->service->submitBatchRequest([
            ['relative_path' => '/tasks/12345', 'method' => ''],
        ]);
    }

    /**
     * Test throws exception for whitespace-only relative_path.
     */
    public function testSubmitBatchRequestThrowsExceptionForWhitespaceRelativePath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Action at index 0 is missing required field(s): relative_path'
        );

        $this->service->submitBatchRequest([
            ['relative_path' => '   ', 'method' => 'get'],
        ]);
    }

    /**
     * Test throws exception for whitespace-only method.
     */
    public function testSubmitBatchRequestThrowsExceptionForWhitespaceMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Action at index 0 is missing required field(s): method'
        );

        $this->service->submitBatchRequest([
            ['relative_path' => '/tasks/12345', 'method' => '   '],
        ]);
    }
}
