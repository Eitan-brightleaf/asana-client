<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\WebhooksApiService;
use BrightleafDigital\Http\AsanaApiClient;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;

class WebhooksApiServiceTest extends TestCase
{
    /** @var AsanaApiClient&\PHPUnit\Framework\MockObject\MockObject */
    private $mockClient;

    /** @var WebhooksApiService */
    private $service;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(AsanaApiClient::class);
        $this->service = new WebhooksApiService($this->mockClient);
    }

    /**
     * Test getWebhooks calls client with correct parameters.
     */
    public function testGetWebhooks(): void
    {
        $workspaceGid = '12345';
        $expectedResponse = [
            ['gid' => '111', 'resource_type' => 'webhook', 'target' => 'https://example.com/webhooks'],
            ['gid' => '222', 'resource_type' => 'webhook', 'target' => 'https://example.com/webhooks2'],
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'webhooks', ['query' => ['workspace' => '12345']], AsanaApiClient::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getWebhooks($workspaceGid);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getWebhooks with additional options.
     */
    public function testGetWebhooksWithOptions(): void
    {
        $workspaceGid = '12345';
        $options = ['resource' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'webhooks',
                ['query' => ['resource' => '67890', 'workspace' => '12345']],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getWebhooks($workspaceGid, $options);
    }

    /**
     * Test getWebhooks with custom response type.
     */
    public function testGetWebhooksWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'webhooks', ['query' => ['workspace' => '12345']], AsanaApiClient::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getWebhooks('12345', [], AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test getWebhooks throws exception for empty workspace GID.
     */
    public function testGetWebhooksThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Workspace GID must be a non-empty string.');

        $this->service->getWebhooks('');
    }

    /**
     * Test getWebhooks throws exception for non-numeric workspace GID.
     */
    public function testGetWebhooksThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Workspace GID must be a numeric string.');

        $this->service->getWebhooks('abc');
    }

    /**
     * Test getWebhook calls client with correct parameters.
     */
    public function testGetWebhook(): void
    {
        $webhookGid = '12345';
        $expectedResponse = [
            'gid' => '12345',
            'resource_type' => 'webhook',
            'target' => 'https://example.com/webhooks',
            'active' => true,
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'webhooks/12345', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getWebhook($webhookGid);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getWebhook with options.
     */
    public function testGetWebhookWithOptions(): void
    {
        $options = ['opt_fields' => 'resource,target,active,filters'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'webhooks/12345', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getWebhook('12345', $options);
    }

    /**
     * Test getWebhook with custom response type.
     */
    public function testGetWebhookWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'webhooks/12345', ['query' => []], AsanaApiClient::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getWebhook('12345', [], AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test getWebhook throws exception for empty GID.
     */
    public function testGetWebhookThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Webhook GID must be a non-empty string.');

        $this->service->getWebhook('');
    }

    /**
     * Test getWebhook throws exception for non-numeric GID.
     */
    public function testGetWebhookThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Webhook GID must be a numeric string.');

        $this->service->getWebhook('abc');
    }

    /**
     * Test createWebhook calls client with correct parameters.
     */
    public function testCreateWebhook(): void
    {
        $data = ['resource' => '12345', 'target' => 'https://example.com/webhooks'];
        $expectedResponse = [
            'gid' => '99999',
            'resource_type' => 'webhook',
            'resource' => ['gid' => '12345'],
            'target' => 'https://example.com/webhooks',
            'active' => true,
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'webhooks',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->createWebhook($data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test createWebhook with options.
     */
    public function testCreateWebhookWithOptions(): void
    {
        $data = ['resource' => '12345', 'target' => 'https://example.com/webhooks'];
        $options = ['opt_fields' => 'resource,target,active'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'webhooks',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createWebhook($data, $options);
    }

    /**
     * Test createWebhook with filters.
     */
    public function testCreateWebhookWithFilters(): void
    {
        $data = [
            'resource' => '12345',
            'target' => 'https://example.com/webhooks',
            'filters' => [
                ['resource_type' => 'task', 'action' => 'changed', 'fields' => ['due_at', 'due_on']],
            ],
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'webhooks',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createWebhook($data);
    }

    /**
     * Test createWebhook throws exception when resource is missing.
     */
    public function testCreateWebhookThrowsExceptionForMissingResource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for webhook creation: resource');

        $this->service->createWebhook(['target' => 'https://example.com/webhooks']);
    }

    /**
     * Test createWebhook throws exception when target is missing.
     */
    public function testCreateWebhookThrowsExceptionForMissingTarget(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for webhook creation: target');

        $this->service->createWebhook(['resource' => '12345']);
    }

    /**
     * Test createWebhook throws exception when both resource and target are missing.
     */
    public function testCreateWebhookThrowsExceptionForMissingBothFields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for webhook creation: resource, target');

        $this->service->createWebhook([]);
    }

    /**
     * Test updateWebhook calls client with correct parameters.
     */
    public function testUpdateWebhook(): void
    {
        $webhookGid = '12345';
        $data = ['filters' => [['resource_type' => 'task', 'action' => 'changed']]];
        $expectedResponse = [
            'gid' => '12345',
            'resource_type' => 'webhook',
            'filters' => [['resource_type' => 'task', 'action' => 'changed']],
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'webhooks/12345',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->updateWebhook($webhookGid, $data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test updateWebhook with options.
     */
    public function testUpdateWebhookWithOptions(): void
    {
        $data = ['filters' => [['resource_type' => 'task', 'action' => 'changed']]];
        $options = ['opt_fields' => 'resource,target,active,filters'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'webhooks/12345',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateWebhook('12345', $data, $options);
    }

    /**
     * Test updateWebhook with custom response type.
     */
    public function testUpdateWebhookWithCustomResponseType(): void
    {
        $data = ['filters' => []];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'webhooks/12345',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->updateWebhook('12345', $data, [], AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test updateWebhook throws exception for empty GID.
     */
    public function testUpdateWebhookThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Webhook GID must be a non-empty string.');

        $this->service->updateWebhook('', ['filters' => []]);
    }

    /**
     * Test updateWebhook throws exception for non-numeric GID.
     */
    public function testUpdateWebhookThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Webhook GID must be a numeric string.');

        $this->service->updateWebhook('abc', ['filters' => []]);
    }

    /**
     * Test deleteWebhook calls client with correct parameters.
     */
    public function testDeleteWebhook(): void
    {
        $webhookGid = '12345';

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'webhooks/12345', [], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $result = $this->service->deleteWebhook($webhookGid);

        $this->assertSame([], $result);
    }

    /**
     * Test deleteWebhook with custom response type.
     */
    public function testDeleteWebhookWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'webhooks/12345', [], AsanaApiClient::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->deleteWebhook('12345', AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test deleteWebhook throws exception for empty GID.
     */
    public function testDeleteWebhookThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Webhook GID must be a non-empty string.');

        $this->service->deleteWebhook('');
    }

    /**
     * Test deleteWebhook throws exception for non-numeric GID.
     */
    public function testDeleteWebhookThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Webhook GID must be a numeric string.');

        $this->service->deleteWebhook('abc');
    }
}
