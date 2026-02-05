<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\PortfoliosApiService;
use BrightleafDigital\Http\AsanaApiClient;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;

class PortfoliosApiServiceTest extends TestCase
{
    /** @var AsanaApiClient&\PHPUnit\Framework\MockObject\MockObject */
    private $mockClient;

    /** @var PortfoliosApiService */
    private $service;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(AsanaApiClient::class);
        $this->service = new PortfoliosApiService($this->mockClient);
    }

    // ── getPortfolios ────────────────────────────────────────────────

    /**
     * Test getPortfolios calls client with correct parameters.
     */
    public function testGetPortfolios(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'name' => 'Portfolio A'],
            ['gid' => '222', 'name' => 'Portfolio B'],
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'portfolios',
                ['query' => ['workspace' => '12345', 'owner' => '67890']],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getPortfolios('12345', '67890');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getPortfolios with additional options.
     */
    public function testGetPortfoliosWithOptions(): void
    {
        $options = ['opt_fields' => 'name,owner'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'portfolios',
                ['query' => ['opt_fields' => 'name,owner', 'workspace' => '12345', 'owner' => '67890']],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getPortfolios('12345', '67890', $options);
    }

    /**
     * Test getPortfolios with custom response type.
     */
    public function testGetPortfoliosWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'portfolios',
                ['query' => ['workspace' => '12345', 'owner' => '67890']],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getPortfolios('12345', '67890', [], AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test getPortfolios throws exception for empty workspace GID.
     */
    public function testGetPortfoliosThrowsExceptionForEmptyWorkspaceGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Workspace GID must be a non-empty string.');

        $this->service->getPortfolios('', '67890');
    }

    /**
     * Test getPortfolios throws exception for non-numeric workspace GID.
     */
    public function testGetPortfoliosThrowsExceptionForNonNumericWorkspaceGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Workspace GID must be a numeric string.');

        $this->service->getPortfolios('abc', '67890');
    }

    /**
     * Test getPortfolios throws exception for empty owner GID.
     */
    public function testGetPortfoliosThrowsExceptionForEmptyOwnerGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Owner GID must be a non-empty string.');

        $this->service->getPortfolios('12345', '');
    }

    /**
     * Test getPortfolios throws exception for non-numeric owner GID.
     */
    public function testGetPortfoliosThrowsExceptionForNonNumericOwnerGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Owner GID must be a numeric string.');

        $this->service->getPortfolios('12345', 'abc');
    }

    // ── getPortfolio ─────────────────────────────────────────────────

    /**
     * Test getPortfolio calls client with correct parameters.
     */
    public function testGetPortfolio(): void
    {
        $expectedResponse = ['gid' => '12345', 'resource_type' => 'portfolio', 'name' => 'My Portfolio'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'portfolios/12345', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getPortfolio('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getPortfolio with options.
     */
    public function testGetPortfolioWithOptions(): void
    {
        $options = ['opt_fields' => 'name,owner,members'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'portfolios/12345', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getPortfolio('12345', $options);
    }

    /**
     * Test getPortfolio with custom response type.
     */
    public function testGetPortfolioWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'portfolios/12345', ['query' => []], AsanaApiClient::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getPortfolio('12345', [], AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test getPortfolio throws exception for empty GID.
     */
    public function testGetPortfolioThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Portfolio GID must be a non-empty string.');

        $this->service->getPortfolio('');
    }

    /**
     * Test getPortfolio throws exception for non-numeric GID.
     */
    public function testGetPortfolioThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Portfolio GID must be a numeric string.');

        $this->service->getPortfolio('abc');
    }

    // ── createPortfolio ──────────────────────────────────────────────

    /**
     * Test createPortfolio calls client with correct parameters.
     */
    public function testCreatePortfolio(): void
    {
        $data = ['name' => 'Product Launches', 'workspace' => '12345'];
        $expectedResponse = ['gid' => '99999', 'resource_type' => 'portfolio', 'name' => 'Product Launches'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'portfolios',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->createPortfolio($data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test createPortfolio with options.
     */
    public function testCreatePortfolioWithOptions(): void
    {
        $data = ['name' => 'Product Launches', 'workspace' => '12345'];
        $options = ['opt_fields' => 'name,owner,workspace'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'portfolios',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createPortfolio($data, $options);
    }

    /**
     * Test createPortfolio with optional fields.
     */
    public function testCreatePortfolioWithOptionalFields(): void
    {
        $data = [
            'name' => 'Product Launches',
            'workspace' => '12345',
            'color' => 'light-green',
            'due_on' => '2026-12-31',
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'portfolios',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createPortfolio($data);
    }

    /**
     * Test createPortfolio throws exception when name is missing.
     */
    public function testCreatePortfolioThrowsExceptionForMissingName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for portfolio creation: name');

        $this->service->createPortfolio(['workspace' => '12345']);
    }

    /**
     * Test createPortfolio throws exception when workspace is missing.
     */
    public function testCreatePortfolioThrowsExceptionForMissingWorkspace(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for portfolio creation: workspace');

        $this->service->createPortfolio(['name' => 'Test']);
    }

    /**
     * Test createPortfolio throws exception when both fields are missing.
     */
    public function testCreatePortfolioThrowsExceptionForMissingBothFields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for portfolio creation: name, workspace');

        $this->service->createPortfolio([]);
    }

    // ── updatePortfolio ──────────────────────────────────────────────

    /**
     * Test updatePortfolio calls client with correct parameters.
     */
    public function testUpdatePortfolio(): void
    {
        $data = ['name' => 'Updated Portfolio'];
        $expectedResponse = ['gid' => '12345', 'name' => 'Updated Portfolio'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'portfolios/12345',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->updatePortfolio('12345', $data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test updatePortfolio with options.
     */
    public function testUpdatePortfolioWithOptions(): void
    {
        $data = ['name' => 'Updated'];
        $options = ['opt_fields' => 'name,owner'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'portfolios/12345',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updatePortfolio('12345', $data, $options);
    }

    /**
     * Test updatePortfolio with custom response type.
     */
    public function testUpdatePortfolioWithCustomResponseType(): void
    {
        $data = ['name' => 'Updated'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'portfolios/12345',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->updatePortfolio('12345', $data, [], AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test updatePortfolio throws exception for empty GID.
     */
    public function testUpdatePortfolioThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Portfolio GID must be a non-empty string.');

        $this->service->updatePortfolio('', ['name' => 'Test']);
    }

    /**
     * Test updatePortfolio throws exception for non-numeric GID.
     */
    public function testUpdatePortfolioThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Portfolio GID must be a numeric string.');

        $this->service->updatePortfolio('abc', ['name' => 'Test']);
    }

    // ── deletePortfolio ──────────────────────────────────────────────

    /**
     * Test deletePortfolio calls client with correct parameters.
     */
    public function testDeletePortfolio(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'portfolios/12345', [], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $result = $this->service->deletePortfolio('12345');

        $this->assertSame([], $result);
    }

    /**
     * Test deletePortfolio with custom response type.
     */
    public function testDeletePortfolioWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'portfolios/12345', [], AsanaApiClient::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->deletePortfolio('12345', AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test deletePortfolio throws exception for empty GID.
     */
    public function testDeletePortfolioThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Portfolio GID must be a non-empty string.');

        $this->service->deletePortfolio('');
    }

    /**
     * Test deletePortfolio throws exception for non-numeric GID.
     */
    public function testDeletePortfolioThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Portfolio GID must be a numeric string.');

        $this->service->deletePortfolio('abc');
    }

    // ── getPortfolioItems ────────────────────────────────────────────

    /**
     * Test getPortfolioItems calls client with correct parameters.
     */
    public function testGetPortfolioItems(): void
    {
        $expectedResponse = [['gid' => '111', 'name' => 'Project A']];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'portfolios/12345/items', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getPortfolioItems('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getPortfolioItems with options.
     */
    public function testGetPortfolioItemsWithOptions(): void
    {
        $options = ['opt_fields' => 'name,owner,due_on'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'portfolios/12345/items', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getPortfolioItems('12345', $options);
    }

    /**
     * Test getPortfolioItems throws exception for empty GID.
     */
    public function testGetPortfolioItemsThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Portfolio GID must be a non-empty string.');

        $this->service->getPortfolioItems('');
    }

    /**
     * Test getPortfolioItems throws exception for non-numeric GID.
     */
    public function testGetPortfolioItemsThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Portfolio GID must be a numeric string.');

        $this->service->getPortfolioItems('abc');
    }

    // ── addItemToPortfolio ───────────────────────────────────────────

    /**
     * Test addItemToPortfolio calls client with correct parameters.
     */
    public function testAddItemToPortfolio(): void
    {
        $data = ['item' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'portfolios/12345/addItem',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addItemToPortfolio('12345', $data);
    }

    /**
     * Test addItemToPortfolio with insert positioning.
     */
    public function testAddItemToPortfolioWithPositioning(): void
    {
        $data = ['item' => '67890', 'insert_after' => '11111'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'portfolios/12345/addItem',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addItemToPortfolio('12345', $data);
    }

    /**
     * Test addItemToPortfolio throws exception for empty GID.
     */
    public function testAddItemToPortfolioThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Portfolio GID must be a non-empty string.');

        $this->service->addItemToPortfolio('', ['item' => '67890']);
    }

    /**
     * Test addItemToPortfolio throws exception for non-numeric GID.
     */
    public function testAddItemToPortfolioThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Portfolio GID must be a numeric string.');

        $this->service->addItemToPortfolio('abc', ['item' => '67890']);
    }

    /**
     * Test addItemToPortfolio throws exception when item field is missing.
     */
    public function testAddItemToPortfolioThrowsExceptionForMissingItem(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for adding item to portfolio: item');

        $this->service->addItemToPortfolio('12345', []);
    }

    // ── removeItemFromPortfolio ──────────────────────────────────────

    /**
     * Test removeItemFromPortfolio calls client with correct parameters.
     */
    public function testRemoveItemFromPortfolio(): void
    {
        $data = ['item' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'portfolios/12345/removeItem',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $result = $this->service->removeItemFromPortfolio('12345', $data);

        $this->assertSame([], $result);
    }

    /**
     * Test removeItemFromPortfolio throws exception for empty GID.
     */
    public function testRemoveItemFromPortfolioThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Portfolio GID must be a non-empty string.');

        $this->service->removeItemFromPortfolio('', ['item' => '67890']);
    }

    /**
     * Test removeItemFromPortfolio throws exception for non-numeric GID.
     */
    public function testRemoveItemFromPortfolioThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Portfolio GID must be a numeric string.');

        $this->service->removeItemFromPortfolio('abc', ['item' => '67890']);
    }

    /**
     * Test removeItemFromPortfolio throws exception when item field is missing.
     */
    public function testRemoveItemFromPortfolioThrowsExceptionForMissingItem(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for removing item from portfolio: item');

        $this->service->removeItemFromPortfolio('12345', []);
    }

    // ── addMembersToPortfolio ────────────────────────────────────────

    /**
     * Test addMembersToPortfolio calls client with correct parameters.
     */
    public function testAddMembersToPortfolio(): void
    {
        $data = ['members' => '67890,11111'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'portfolios/12345/addMembers',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addMembersToPortfolio('12345', $data);
    }

    /**
     * Test addMembersToPortfolio with options.
     */
    public function testAddMembersToPortfolioWithOptions(): void
    {
        $data = ['members' => '67890'];
        $options = ['opt_fields' => 'members'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'portfolios/12345/addMembers',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addMembersToPortfolio('12345', $data, $options);
    }

    /**
     * Test addMembersToPortfolio throws exception for empty GID.
     */
    public function testAddMembersToPortfolioThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Portfolio GID must be a non-empty string.');

        $this->service->addMembersToPortfolio('', ['members' => '67890']);
    }

    /**
     * Test addMembersToPortfolio throws exception for non-numeric GID.
     */
    public function testAddMembersToPortfolioThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Portfolio GID must be a numeric string.');

        $this->service->addMembersToPortfolio('abc', ['members' => '67890']);
    }

    /**
     * Test addMembersToPortfolio throws exception when members field is missing.
     */
    public function testAddMembersToPortfolioThrowsExceptionForMissingMembers(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for adding members to portfolio: members');

        $this->service->addMembersToPortfolio('12345', []);
    }

    // ── removeMembersFromPortfolio ───────────────────────────────────

    /**
     * Test removeMembersFromPortfolio calls client with correct parameters.
     */
    public function testRemoveMembersFromPortfolio(): void
    {
        $data = ['members' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'portfolios/12345/removeMembers',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $result = $this->service->removeMembersFromPortfolio('12345', $data);

        $this->assertSame([], $result);
    }

    /**
     * Test removeMembersFromPortfolio with options.
     */
    public function testRemoveMembersFromPortfolioWithOptions(): void
    {
        $data = ['members' => '67890'];
        $options = ['opt_fields' => 'members'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'portfolios/12345/removeMembers',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->removeMembersFromPortfolio('12345', $data, $options);
    }

    /**
     * Test removeMembersFromPortfolio throws exception for empty GID.
     */
    public function testRemoveMembersFromPortfolioThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Portfolio GID must be a non-empty string.');

        $this->service->removeMembersFromPortfolio('', ['members' => '67890']);
    }

    /**
     * Test removeMembersFromPortfolio throws exception for non-numeric GID.
     */
    public function testRemoveMembersFromPortfolioThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Portfolio GID must be a numeric string.');

        $this->service->removeMembersFromPortfolio('abc', ['members' => '67890']);
    }

    /**
     * Test removeMembersFromPortfolio throws exception when members field is missing.
     */
    public function testRemoveMembersFromPortfolioThrowsExceptionForMissingMembers(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for removing members from portfolio: members');

        $this->service->removeMembersFromPortfolio('12345', []);
    }
}
