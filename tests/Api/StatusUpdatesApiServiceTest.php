<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\StatusUpdatesApiService;
use BrightleafDigital\Http\AsanaApiClient;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;

class StatusUpdatesApiServiceTest extends TestCase
{
    /** @var AsanaApiClient&\PHPUnit\Framework\MockObject\MockObject */
    private $mockClient;

    /** @var StatusUpdatesApiService */
    private $service;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(AsanaApiClient::class);
        $this->service = new StatusUpdatesApiService($this->mockClient);
    }

    // ── getStatusUpdate ─────────────────────────────────────────────────

    /**
     * Test getStatusUpdate calls client with correct parameters.
     */
    public function testGetStatusUpdate(): void
    {
        $expectedResponse = [
            'gid' => '12345',
            'resource_type' => 'status_update',
            'title' => 'Status Update',
            'text' => 'Project is on track.',
            'status_type' => 'on_track',
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'status_updates/12345',
                ['query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getStatusUpdate('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getStatusUpdate with options.
     */
    public function testGetStatusUpdateWithOptions(): void
    {
        $options = ['opt_fields' => 'title,text,status_type,author'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'status_updates/12345',
                ['query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getStatusUpdate('12345', $options);
    }

    /**
     * Test getStatusUpdate with custom response type.
     */
    public function testGetStatusUpdateWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'status_updates/12345',
                ['query' => []],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getStatusUpdate(
            '12345',
            [],
            AsanaApiClient::RESPONSE_FULL
        );
    }

    /**
     * Test getStatusUpdate throws exception for empty GID.
     */
    public function testGetStatusUpdateThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Status Update GID must be a non-empty string.'
        );

        $this->service->getStatusUpdate('');
    }

    /**
     * Test getStatusUpdate throws exception for non-numeric GID.
     */
    public function testGetStatusUpdateThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Status Update GID must be a numeric string.'
        );

        $this->service->getStatusUpdate('abc');
    }

    // ── getStatusUpdatesForObject ───────────────────────────────────────

    /**
     * Test getStatusUpdatesForObject calls client with correct parameters.
     */
    public function testGetStatusUpdatesForObject(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'title' => 'Update A'],
            ['gid' => '222', 'title' => 'Update B'],
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'status_updates',
                ['query' => ['parent' => '12345']],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getStatusUpdatesForObject('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getStatusUpdatesForObject with options.
     */
    public function testGetStatusUpdatesForObjectWithOptions(): void
    {
        $options = ['opt_fields' => 'title,text,status_type'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'status_updates',
                ['query' => [
                    'opt_fields' => 'title,text,status_type',
                    'parent' => '12345',
                ]],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getStatusUpdatesForObject('12345', $options);
    }

    /**
     * Test getStatusUpdatesForObject with created_since filter.
     */
    public function testGetStatusUpdatesForObjectWithCreatedSince(): void
    {
        $options = ['created_since' => '2026-01-01T00:00:00.000Z'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'status_updates',
                ['query' => [
                    'created_since' => '2026-01-01T00:00:00.000Z',
                    'parent' => '12345',
                ]],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getStatusUpdatesForObject('12345', $options);
    }

    /**
     * Test getStatusUpdatesForObject with custom response type.
     */
    public function testGetStatusUpdatesForObjectWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'status_updates',
                ['query' => ['parent' => '12345']],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getStatusUpdatesForObject(
            '12345',
            [],
            AsanaApiClient::RESPONSE_FULL
        );
    }

    /**
     * Test getStatusUpdatesForObject throws exception for empty GID.
     */
    public function testGetStatusUpdatesForObjectThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Parent GID must be a non-empty string.'
        );

        $this->service->getStatusUpdatesForObject('');
    }

    /**
     * Test getStatusUpdatesForObject throws exception for non-numeric GID.
     */
    public function testGetStatusUpdatesForObjectThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Parent GID must be a numeric string.'
        );

        $this->service->getStatusUpdatesForObject('abc');
    }

    // ── createStatusUpdate ──────────────────────────────────────────────

    /**
     * Test createStatusUpdate calls client with correct parameters.
     */
    public function testCreateStatusUpdate(): void
    {
        $data = [
            'parent' => '12345',
            'text' => 'Project is on track.',
            'status_type' => 'on_track',
        ];
        $expectedResponse = [
            'gid' => '99999',
            'resource_type' => 'status_update',
            'text' => 'Project is on track.',
            'status_type' => 'on_track',
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'status_updates',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->createStatusUpdate($data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test createStatusUpdate with options.
     */
    public function testCreateStatusUpdateWithOptions(): void
    {
        $data = [
            'parent' => '12345',
            'text' => 'Project is on track.',
            'status_type' => 'on_track',
        ];
        $options = ['opt_fields' => 'title,text,status_type,author'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'status_updates',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createStatusUpdate($data, $options);
    }

    /**
     * Test createStatusUpdate with optional title field.
     */
    public function testCreateStatusUpdateWithOptionalFields(): void
    {
        $data = [
            'parent' => '12345',
            'text' => 'Project is on track for Q1 delivery.',
            'status_type' => 'on_track',
            'title' => 'Q1 Status Report',
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'status_updates',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createStatusUpdate($data);
    }

    /**
     * Test createStatusUpdate with custom response type.
     */
    public function testCreateStatusUpdateWithCustomResponseType(): void
    {
        $data = [
            'parent' => '12345',
            'text' => 'On track.',
            'status_type' => 'on_track',
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'status_updates',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->createStatusUpdate(
            $data,
            [],
            AsanaApiClient::RESPONSE_FULL
        );
    }

    /**
     * Test createStatusUpdate throws exception when parent is missing.
     */
    public function testCreateStatusUpdateThrowsExceptionForMissingParent(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Missing required field(s) for status update creation: parent'
        );

        $this->service->createStatusUpdate([
            'text' => 'On track.',
            'status_type' => 'on_track',
        ]);
    }

    /**
     * Test createStatusUpdate throws exception when text is missing.
     */
    public function testCreateStatusUpdateThrowsExceptionForMissingText(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Missing required field(s) for status update creation: text'
        );

        $this->service->createStatusUpdate([
            'parent' => '12345',
            'status_type' => 'on_track',
        ]);
    }

    /**
     * Test createStatusUpdate throws exception when status_type is missing.
     */
    public function testCreateStatusUpdateThrowsExceptionForMissingStatusType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Missing required field(s) for status update creation: status_type'
        );

        $this->service->createStatusUpdate([
            'parent' => '12345',
            'text' => 'On track.',
        ]);
    }

    /**
     * Test createStatusUpdate throws exception when all fields are missing.
     */
    public function testCreateStatusUpdateThrowsExceptionForMissingAllFields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Missing required field(s) for status update creation: '
            . 'parent, text, status_type'
        );

        $this->service->createStatusUpdate([]);
    }

    // ── deleteStatusUpdate ──────────────────────────────────────────────

    /**
     * Test deleteStatusUpdate calls client with correct parameters.
     */
    public function testDeleteStatusUpdate(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'DELETE',
                'status_updates/12345',
                [],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $result = $this->service->deleteStatusUpdate('12345');

        $this->assertSame([], $result);
    }

    /**
     * Test deleteStatusUpdate with custom response type.
     */
    public function testDeleteStatusUpdateWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'DELETE',
                'status_updates/12345',
                [],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->deleteStatusUpdate(
            '12345',
            AsanaApiClient::RESPONSE_FULL
        );
    }

    /**
     * Test deleteStatusUpdate throws exception for empty GID.
     */
    public function testDeleteStatusUpdateThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Status Update GID must be a non-empty string.'
        );

        $this->service->deleteStatusUpdate('');
    }

    /**
     * Test deleteStatusUpdate throws exception for non-numeric GID.
     */
    public function testDeleteStatusUpdateThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Status Update GID must be a numeric string.'
        );

        $this->service->deleteStatusUpdate('abc');
    }
}
