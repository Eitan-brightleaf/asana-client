<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\GoalsApiService;
use BrightleafDigital\Http\AsanaApiClient;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;

class GoalsApiServiceTest extends TestCase
{
    /** @var AsanaApiClient&\PHPUnit\Framework\MockObject\MockObject */
    private $mockClient;

    /** @var GoalsApiService */
    private $service;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(AsanaApiClient::class);
        $this->service = new GoalsApiService($this->mockClient);
    }

    // ── getGoals ────────────────────────────────────────────────────────

    /**
     * Test getGoals calls client with correct parameters.
     */
    public function testGetGoals(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'name' => 'Goal A'],
            ['gid' => '222', 'name' => 'Goal B'],
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'goals',
                ['query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getGoals();

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getGoals with filtering options.
     */
    public function testGetGoalsWithOptions(): void
    {
        $options = ['workspace' => '12345', 'opt_fields' => 'name,owner'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'goals',
                ['query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getGoals($options);
    }

    /**
     * Test getGoals with custom response type.
     */
    public function testGetGoalsWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'goals',
                ['query' => []],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getGoals([], AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test getGoals with workspace and team filters.
     */
    public function testGetGoalsWithMultipleFilters(): void
    {
        $options = [
            'workspace' => '12345',
            'team' => '67890',
            'is_workspace_level' => true,
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'goals',
                ['query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getGoals($options);
    }

    // ── getGoal ─────────────────────────────────────────────────────────

    /**
     * Test getGoal calls client with correct parameters.
     */
    public function testGetGoal(): void
    {
        $expectedResponse = ['gid' => '12345', 'resource_type' => 'goal', 'name' => 'My Goal'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'goals/12345', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getGoal('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getGoal with options.
     */
    public function testGetGoalWithOptions(): void
    {
        $options = ['opt_fields' => 'name,owner,workspace'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'goals/12345', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getGoal('12345', $options);
    }

    /**
     * Test getGoal with custom response type.
     */
    public function testGetGoalWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'goals/12345', ['query' => []], AsanaApiClient::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getGoal('12345', [], AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test getGoal throws exception for empty GID.
     */
    public function testGetGoalThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->getGoal('');
    }

    /**
     * Test getGoal throws exception for non-numeric GID.
     */
    public function testGetGoalThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->getGoal('abc');
    }

    // ── createGoal ──────────────────────────────────────────────────────

    /**
     * Test createGoal calls client with correct parameters.
     */
    public function testCreateGoal(): void
    {
        $data = ['name' => 'Increase revenue by 20%', 'workspace' => '12345'];
        $expectedResponse = ['gid' => '99999', 'resource_type' => 'goal', 'name' => 'Increase revenue by 20%'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->createGoal($data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test createGoal with options.
     */
    public function testCreateGoalWithOptions(): void
    {
        $data = ['name' => 'Increase revenue by 20%', 'workspace' => '12345'];
        $options = ['opt_fields' => 'name,owner,workspace'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createGoal($data, $options);
    }

    /**
     * Test createGoal with optional fields.
     */
    public function testCreateGoalWithOptionalFields(): void
    {
        $data = [
            'name' => 'Increase revenue by 20%',
            'workspace' => '12345',
            'due_on' => '2026-12-31',
            'owner' => '67890',
            'notes' => 'Important business goal',
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createGoal($data);
    }

    /**
     * Test createGoal throws exception when name is missing.
     */
    public function testCreateGoalThrowsExceptionForMissingName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for goal creation: name');

        $this->service->createGoal(['workspace' => '12345']);
    }

    /**
     * Test createGoal throws exception when workspace is missing.
     */
    public function testCreateGoalThrowsExceptionForMissingWorkspace(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for goal creation: workspace');

        $this->service->createGoal(['name' => 'Test']);
    }

    /**
     * Test createGoal throws exception when both fields are missing.
     */
    public function testCreateGoalThrowsExceptionForMissingBothFields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for goal creation: name, workspace');

        $this->service->createGoal([]);
    }

    // ── updateGoal ──────────────────────────────────────────────────────

    /**
     * Test updateGoal calls client with correct parameters.
     */
    public function testUpdateGoal(): void
    {
        $data = ['name' => 'Updated Goal'];
        $expectedResponse = ['gid' => '12345', 'name' => 'Updated Goal'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'goals/12345',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->updateGoal('12345', $data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test updateGoal with options.
     */
    public function testUpdateGoalWithOptions(): void
    {
        $data = ['name' => 'Updated'];
        $options = ['opt_fields' => 'name,owner'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'goals/12345',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateGoal('12345', $data, $options);
    }

    /**
     * Test updateGoal with custom response type.
     */
    public function testUpdateGoalWithCustomResponseType(): void
    {
        $data = ['name' => 'Updated'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'goals/12345',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->updateGoal('12345', $data, [], AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test updateGoal throws exception for empty GID.
     */
    public function testUpdateGoalThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->updateGoal('', ['name' => 'Test']);
    }

    /**
     * Test updateGoal throws exception for non-numeric GID.
     */
    public function testUpdateGoalThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->updateGoal('abc', ['name' => 'Test']);
    }

    // ── deleteGoal ──────────────────────────────────────────────────────

    /**
     * Test deleteGoal calls client with correct parameters.
     */
    public function testDeleteGoal(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'goals/12345', [], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $result = $this->service->deleteGoal('12345');

        $this->assertSame([], $result);
    }

    /**
     * Test deleteGoal with custom response type.
     */
    public function testDeleteGoalWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'goals/12345', [], AsanaApiClient::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->deleteGoal('12345', AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test deleteGoal throws exception for empty GID.
     */
    public function testDeleteGoalThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->deleteGoal('');
    }

    /**
     * Test deleteGoal throws exception for non-numeric GID.
     */
    public function testDeleteGoalThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->deleteGoal('abc');
    }

    // ── getParentGoalsForGoal ───────────────────────────────────────────

    /**
     * Test getParentGoalsForGoal calls client with correct parameters.
     */
    public function testGetParentGoalsForGoal(): void
    {
        $expectedResponse = [['gid' => '111', 'name' => 'Parent Goal']];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'goals/12345/parentGoals', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getParentGoalsForGoal('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getParentGoalsForGoal with options.
     */
    public function testGetParentGoalsForGoalWithOptions(): void
    {
        $options = ['opt_fields' => 'name,owner,workspace'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'goals/12345/parentGoals', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getParentGoalsForGoal('12345', $options);
    }

    /**
     * Test getParentGoalsForGoal with custom response type.
     */
    public function testGetParentGoalsForGoalWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'goals/12345/parentGoals', ['query' => []], AsanaApiClient::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getParentGoalsForGoal('12345', [], AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test getParentGoalsForGoal throws exception for empty GID.
     */
    public function testGetParentGoalsForGoalThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->getParentGoalsForGoal('');
    }

    /**
     * Test getParentGoalsForGoal throws exception for non-numeric GID.
     */
    public function testGetParentGoalsForGoalThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->getParentGoalsForGoal('abc');
    }

    // ── addSubgoal ──────────────────────────────────────────────────────

    /**
     * Test addSubgoal calls client with correct parameters.
     */
    public function testAddSubgoal(): void
    {
        $data = ['subgoal' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/addSubgoal',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addSubgoal('12345', $data);
    }

    /**
     * Test addSubgoal with insert positioning.
     */
    public function testAddSubgoalWithPositioning(): void
    {
        $data = ['subgoal' => '67890', 'insert_after' => '11111'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/addSubgoal',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addSubgoal('12345', $data);
    }

    /**
     * Test addSubgoal with options.
     */
    public function testAddSubgoalWithOptions(): void
    {
        $data = ['subgoal' => '67890'];
        $options = ['opt_fields' => 'name'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/addSubgoal',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addSubgoal('12345', $data, $options);
    }

    /**
     * Test addSubgoal throws exception for empty GID.
     */
    public function testAddSubgoalThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->addSubgoal('', ['subgoal' => '67890']);
    }

    /**
     * Test addSubgoal throws exception for non-numeric GID.
     */
    public function testAddSubgoalThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->addSubgoal('abc', ['subgoal' => '67890']);
    }

    /**
     * Test addSubgoal throws exception when subgoal field is missing.
     */
    public function testAddSubgoalThrowsExceptionForMissingSubgoal(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for adding subgoal to goal: subgoal');

        $this->service->addSubgoal('12345', []);
    }

    // ── removeSubgoal ───────────────────────────────────────────────────

    /**
     * Test removeSubgoal calls client with correct parameters.
     */
    public function testRemoveSubgoal(): void
    {
        $data = ['subgoal' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/removeSubgoal',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $result = $this->service->removeSubgoal('12345', $data);

        $this->assertSame([], $result);
    }

    /**
     * Test removeSubgoal with options.
     */
    public function testRemoveSubgoalWithOptions(): void
    {
        $data = ['subgoal' => '67890'];
        $options = ['opt_fields' => 'name'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/removeSubgoal',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->removeSubgoal('12345', $data, $options);
    }

    /**
     * Test removeSubgoal throws exception for empty GID.
     */
    public function testRemoveSubgoalThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->removeSubgoal('', ['subgoal' => '67890']);
    }

    /**
     * Test removeSubgoal throws exception for non-numeric GID.
     */
    public function testRemoveSubgoalThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->removeSubgoal('abc', ['subgoal' => '67890']);
    }

    /**
     * Test removeSubgoal throws exception when subgoal field is missing.
     */
    public function testRemoveSubgoalThrowsExceptionForMissingSubgoal(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for removing subgoal from goal: subgoal');

        $this->service->removeSubgoal('12345', []);
    }

    // ── addSupportingWorkForGoal ────────────────────────────────────────

    /**
     * Test addSupportingWorkForGoal calls client with correct parameters.
     */
    public function testAddSupportingWorkForGoal(): void
    {
        $data = ['supporting_work' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/addSupportingWork',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addSupportingWorkForGoal('12345', $data);
    }

    /**
     * Test addSupportingWorkForGoal with options.
     */
    public function testAddSupportingWorkForGoalWithOptions(): void
    {
        $data = ['supporting_work' => '67890'];
        $options = ['opt_fields' => 'name'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/addSupportingWork',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addSupportingWorkForGoal('12345', $data, $options);
    }

    /**
     * Test addSupportingWorkForGoal with custom response type.
     */
    public function testAddSupportingWorkForGoalWithCustomResponseType(): void
    {
        $data = ['supporting_work' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/addSupportingWork',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->addSupportingWorkForGoal('12345', $data, [], AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test addSupportingWorkForGoal throws exception for empty GID.
     */
    public function testAddSupportingWorkForGoalThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->addSupportingWorkForGoal('', ['supporting_work' => '67890']);
    }

    /**
     * Test addSupportingWorkForGoal throws exception for non-numeric GID.
     */
    public function testAddSupportingWorkForGoalThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->addSupportingWorkForGoal('abc', ['supporting_work' => '67890']);
    }

    /**
     * Test addSupportingWorkForGoal throws exception when supporting_work field is missing.
     */
    public function testAddSupportingWorkForGoalThrowsExceptionForMissingSupportingWork(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for adding supporting work to goal: supporting_work');

        $this->service->addSupportingWorkForGoal('12345', []);
    }

    // ── removeSupportingWorkForGoal ─────────────────────────────────────

    /**
     * Test removeSupportingWorkForGoal calls client with correct parameters.
     */
    public function testRemoveSupportingWorkForGoal(): void
    {
        $data = ['supporting_work' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/removeSupportingWork',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $result = $this->service->removeSupportingWorkForGoal('12345', $data);

        $this->assertSame([], $result);
    }

    /**
     * Test removeSupportingWorkForGoal with options.
     */
    public function testRemoveSupportingWorkForGoalWithOptions(): void
    {
        $data = ['supporting_work' => '67890'];
        $options = ['opt_fields' => 'name'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/removeSupportingWork',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->removeSupportingWorkForGoal('12345', $data, $options);
    }

    /**
     * Test removeSupportingWorkForGoal throws exception for empty GID.
     */
    public function testRemoveSupportingWorkForGoalThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->removeSupportingWorkForGoal('', ['supporting_work' => '67890']);
    }

    /**
     * Test removeSupportingWorkForGoal throws exception for non-numeric GID.
     */
    public function testRemoveSupportingWorkForGoalThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->removeSupportingWorkForGoal('abc', ['supporting_work' => '67890']);
    }

    /**
     * Test removeSupportingWorkForGoal throws exception when supporting_work field is missing.
     */
    public function testRemoveSupportingWorkForGoalThrowsExceptionForMissingSupportingWork(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Missing required field(s) for removing supporting work from goal: supporting_work'
        );

        $this->service->removeSupportingWorkForGoal('12345', []);
    }
}
