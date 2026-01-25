<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\MembershipApiService;
use BrightleafDigital\Http\AsanaApiClient;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;

class MembershipApiServiceTest extends TestCase
{
    private AsanaApiClient $mockClient;
    private MembershipApiService $service;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(AsanaApiClient::class);
        $this->service = new MembershipApiService($this->mockClient);
    }

    /**
     * Test getMemberships calls client with correct parameters.
     */
    public function testGetMemberships(): void
    {
        $options = ['parent' => '12345'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'memberships', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getMemberships($options);
    }

    /**
     * Test getMemberships with portfolio filter.
     */
    public function testGetMembershipsWithPortfolio(): void
    {
        $options = ['portfolio' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'memberships', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getMemberships($options);
    }

    /**
     * Test getMemberships with member filter.
     */
    public function testGetMembershipsWithMemberFilter(): void
    {
        $options = ['parent' => '12345', 'member' => 'user123'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'memberships', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getMemberships($options);
    }

    /**
     * Test createMembership calls client with correct parameters.
     */
    public function testCreateMembership(): void
    {
        $data = ['parent' => '12345', 'member' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'memberships',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createMembership($data);
    }

    /**
     * Test createMembership with access_level.
     */
    public function testCreateMembershipWithAccessLevel(): void
    {
        $data = ['parent' => '12345', 'member' => '67890', 'access_level' => 'editor'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'memberships',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createMembership($data);
    }

    /**
     * Test getMembership calls client with correct parameters.
     */
    public function testGetMembership(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'memberships/12345', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getMembership('12345');
    }

    /**
     * Test getMembership with options.
     */
    public function testGetMembershipWithOptions(): void
    {
        $options = ['opt_fields' => 'access_level,member,parent'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'memberships/12345', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getMembership('12345', $options);
    }

    /**
     * Test updateMembership calls client with correct parameters.
     */
    public function testUpdateMembership(): void
    {
        $data = ['access_level' => 'admin'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'memberships/12345',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateMembership('12345', $data);
    }

    /**
     * Test deleteMembership calls client with correct parameters.
     */
    public function testDeleteMembership(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'memberships/12345', [], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->deleteMembership('12345');
    }

    /**
     * Test methods with custom response type.
     */
    public function testGetMembershipWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'memberships/12345', ['query' => []], AsanaApiClient::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getMembership('12345', [], AsanaApiClient::RESPONSE_FULL);
    }
}
