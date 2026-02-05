<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\TimeTrackingEntriesApiService;
use BrightleafDigital\Http\AsanaApiClient;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;

class TimeTrackingEntriesApiServiceTest extends TestCase
{
    /** @var AsanaApiClient&\PHPUnit\Framework\MockObject\MockObject */
    private $mockClient;

    /** @var TimeTrackingEntriesApiService */
    private $service;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(AsanaApiClient::class);
        $this->service = new TimeTrackingEntriesApiService($this->mockClient);
    }

    // ── getTimeTrackingEntry ────────────────────────────────────────────

    /**
     * Test getTimeTrackingEntry calls client with correct parameters.
     */
    public function testGetTimeTrackingEntry(): void
    {
        $expectedResponse = [
            'gid' => '12345',
            'resource_type' => 'time_tracking_entry',
            'duration_minutes' => 60,
            'entered_on' => '2026-02-05',
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'time_tracking_entries/12345',
                ['query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getTimeTrackingEntry('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getTimeTrackingEntry with options.
     */
    public function testGetTimeTrackingEntryWithOptions(): void
    {
        $options = ['opt_fields' => 'duration_minutes,entered_on,created_by'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'time_tracking_entries/12345',
                ['query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getTimeTrackingEntry('12345', $options);
    }

    /**
     * Test getTimeTrackingEntry with custom response type.
     */
    public function testGetTimeTrackingEntryWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'time_tracking_entries/12345',
                ['query' => []],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getTimeTrackingEntry(
            '12345',
            [],
            AsanaApiClient::RESPONSE_FULL
        );
    }

    /**
     * Test getTimeTrackingEntry throws exception for empty GID.
     */
    public function testGetTimeTrackingEntryThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Time Tracking Entry GID must be a non-empty string.'
        );

        $this->service->getTimeTrackingEntry('');
    }

    /**
     * Test getTimeTrackingEntry throws exception for non-numeric GID.
     */
    public function testGetTimeTrackingEntryThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Time Tracking Entry GID must be a numeric string.'
        );

        $this->service->getTimeTrackingEntry('abc');
    }

    // ── getTimeTrackingEntriesForTask ───────────────────────────────────

    /**
     * Test getTimeTrackingEntriesForTask calls client with correct parameters.
     */
    public function testGetTimeTrackingEntriesForTask(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'duration_minutes' => 30],
            ['gid' => '222', 'duration_minutes' => 60],
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'tasks/12345/time_tracking_entries',
                ['query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getTimeTrackingEntriesForTask('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getTimeTrackingEntriesForTask with options.
     */
    public function testGetTimeTrackingEntriesForTaskWithOptions(): void
    {
        $options = [
            'opt_fields' => 'duration_minutes,entered_on',
            'limit' => 50,
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'tasks/12345/time_tracking_entries',
                ['query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getTimeTrackingEntriesForTask('12345', $options);
    }

    /**
     * Test getTimeTrackingEntriesForTask with custom response type.
     */
    public function testGetTimeTrackingEntriesForTaskWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'tasks/12345/time_tracking_entries',
                ['query' => []],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getTimeTrackingEntriesForTask(
            '12345',
            [],
            AsanaApiClient::RESPONSE_FULL
        );
    }

    /**
     * Test getTimeTrackingEntriesForTask throws exception for empty GID.
     */
    public function testGetTimeTrackingEntriesForTaskThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Task GID must be a non-empty string.'
        );

        $this->service->getTimeTrackingEntriesForTask('');
    }

    /**
     * Test getTimeTrackingEntriesForTask throws exception for non-numeric GID.
     */
    public function testGetTimeTrackingEntriesForTaskThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Task GID must be a numeric string.'
        );

        $this->service->getTimeTrackingEntriesForTask('abc');
    }

    // ── createTimeTrackingEntry ─────────────────────────────────────────

    /**
     * Test createTimeTrackingEntry calls client with correct parameters.
     */
    public function testCreateTimeTrackingEntry(): void
    {
        $data = ['entered_on' => '2026-02-05', 'duration_minutes' => 60];
        $expectedResponse = [
            'gid' => '99999',
            'resource_type' => 'time_tracking_entry',
            'entered_on' => '2026-02-05',
            'duration_minutes' => 60,
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks/12345/time_tracking_entries',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->createTimeTrackingEntry('12345', $data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test createTimeTrackingEntry with options.
     */
    public function testCreateTimeTrackingEntryWithOptions(): void
    {
        $data = ['entered_on' => '2026-02-05', 'duration_minutes' => 60];
        $options = ['opt_fields' => 'duration_minutes,entered_on,created_by'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks/12345/time_tracking_entries',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createTimeTrackingEntry('12345', $data, $options);
    }

    /**
     * Test createTimeTrackingEntry with optional created_by field.
     */
    public function testCreateTimeTrackingEntryWithOptionalFields(): void
    {
        $data = [
            'entered_on' => '2026-02-05',
            'duration_minutes' => 120,
            'created_by' => '67890',
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks/12345/time_tracking_entries',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createTimeTrackingEntry('12345', $data);
    }

    /**
     * Test createTimeTrackingEntry with custom response type.
     */
    public function testCreateTimeTrackingEntryWithCustomResponseType(): void
    {
        $data = ['entered_on' => '2026-02-05', 'duration_minutes' => 60];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks/12345/time_tracking_entries',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->createTimeTrackingEntry(
            '12345',
            $data,
            [],
            AsanaApiClient::RESPONSE_FULL
        );
    }

    /**
     * Test createTimeTrackingEntry throws exception for empty task GID.
     */
    public function testCreateTimeTrackingEntryThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Task GID must be a non-empty string.'
        );

        $this->service->createTimeTrackingEntry(
            '',
            ['entered_on' => '2026-02-05', 'duration_minutes' => 60]
        );
    }

    /**
     * Test createTimeTrackingEntry throws exception for non-numeric task GID.
     */
    public function testCreateTimeTrackingEntryThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Task GID must be a numeric string.'
        );

        $this->service->createTimeTrackingEntry(
            'abc',
            ['entered_on' => '2026-02-05', 'duration_minutes' => 60]
        );
    }

    /**
     * Test createTimeTrackingEntry throws exception when entered_on is missing.
     */
    public function testCreateTimeTrackingEntryThrowsExceptionForMissingEnteredOn(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Missing required field(s) for time tracking entry creation: entered_on'
        );

        $this->service->createTimeTrackingEntry(
            '12345',
            ['duration_minutes' => 60]
        );
    }

    /**
     * Test createTimeTrackingEntry throws exception when duration_minutes is missing.
     */
    public function testCreateTimeTrackingEntryThrowsExceptionForMissingDuration(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Missing required field(s) for time tracking entry creation: duration_minutes'
        );

        $this->service->createTimeTrackingEntry(
            '12345',
            ['entered_on' => '2026-02-05']
        );
    }

    /**
     * Test createTimeTrackingEntry throws exception when both fields are missing.
     */
    public function testCreateTimeTrackingEntryThrowsExceptionForMissingBothFields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Missing required field(s) for time tracking entry creation: '
            . 'entered_on, duration_minutes'
        );

        $this->service->createTimeTrackingEntry('12345', []);
    }

    // ── updateTimeTrackingEntry ─────────────────────────────────────────

    /**
     * Test updateTimeTrackingEntry calls client with correct parameters.
     */
    public function testUpdateTimeTrackingEntry(): void
    {
        $data = ['duration_minutes' => 90];
        $expectedResponse = [
            'gid' => '12345',
            'duration_minutes' => 90,
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'time_tracking_entries/12345',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->updateTimeTrackingEntry('12345', $data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test updateTimeTrackingEntry with options.
     */
    public function testUpdateTimeTrackingEntryWithOptions(): void
    {
        $data = ['duration_minutes' => 90];
        $options = ['opt_fields' => 'duration_minutes,entered_on'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'time_tracking_entries/12345',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateTimeTrackingEntry('12345', $data, $options);
    }

    /**
     * Test updateTimeTrackingEntry with custom response type.
     */
    public function testUpdateTimeTrackingEntryWithCustomResponseType(): void
    {
        $data = ['duration_minutes' => 90];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'time_tracking_entries/12345',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->updateTimeTrackingEntry(
            '12345',
            $data,
            [],
            AsanaApiClient::RESPONSE_FULL
        );
    }

    /**
     * Test updateTimeTrackingEntry throws exception for empty GID.
     */
    public function testUpdateTimeTrackingEntryThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Time Tracking Entry GID must be a non-empty string.'
        );

        $this->service->updateTimeTrackingEntry(
            '',
            ['duration_minutes' => 90]
        );
    }

    /**
     * Test updateTimeTrackingEntry throws exception for non-numeric GID.
     */
    public function testUpdateTimeTrackingEntryThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Time Tracking Entry GID must be a numeric string.'
        );

        $this->service->updateTimeTrackingEntry(
            'abc',
            ['duration_minutes' => 90]
        );
    }

    // ── deleteTimeTrackingEntry ─────────────────────────────────────────

    /**
     * Test deleteTimeTrackingEntry calls client with correct parameters.
     */
    public function testDeleteTimeTrackingEntry(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'DELETE',
                'time_tracking_entries/12345',
                [],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $result = $this->service->deleteTimeTrackingEntry('12345');

        $this->assertSame([], $result);
    }

    /**
     * Test deleteTimeTrackingEntry with custom response type.
     */
    public function testDeleteTimeTrackingEntryWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'DELETE',
                'time_tracking_entries/12345',
                [],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->deleteTimeTrackingEntry(
            '12345',
            AsanaApiClient::RESPONSE_FULL
        );
    }

    /**
     * Test deleteTimeTrackingEntry throws exception for empty GID.
     */
    public function testDeleteTimeTrackingEntryThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Time Tracking Entry GID must be a non-empty string.'
        );

        $this->service->deleteTimeTrackingEntry('');
    }

    /**
     * Test deleteTimeTrackingEntry throws exception for non-numeric GID.
     */
    public function testDeleteTimeTrackingEntryThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Time Tracking Entry GID must be a numeric string.'
        );

        $this->service->deleteTimeTrackingEntry('abc');
    }
}
