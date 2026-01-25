<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\CustomFieldApiService;
use BrightleafDigital\Http\AsanaApiClient;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;

class CustomFieldApiServiceTest extends TestCase
{
    private AsanaApiClient $mockClient;
    private CustomFieldApiService $service;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(AsanaApiClient::class);
        $this->service = new CustomFieldApiService($this->mockClient);
    }

    /**
     * Test createCustomField calls client with correct parameters.
     */
    public function testCreateCustomField(): void
    {
        $data = [
            'workspace' => '12345',
            'name' => 'Priority',
            'resource_subtype' => 'enum'
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'custom_fields',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createCustomField($data);
    }

    /**
     * Test createCustomField with enum options.
     */
    public function testCreateCustomFieldWithEnumOptions(): void
    {
        $data = [
            'workspace' => '12345',
            'name' => 'Priority',
            'resource_subtype' => 'enum',
            'enum_options' => [
                ['name' => 'High', 'color' => 'red'],
                ['name' => 'Medium', 'color' => 'yellow'],
                ['name' => 'Low', 'color' => 'green']
            ]
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'custom_fields',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createCustomField($data);
    }

    /**
     * Test getCustomField calls client with correct parameters.
     */
    public function testGetCustomField(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'custom_fields/12345', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getCustomField('12345');
    }

    /**
     * Test getCustomField with options.
     */
    public function testGetCustomFieldWithOptions(): void
    {
        $options = ['opt_fields' => 'name,resource_subtype,enum_options'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'custom_fields/12345', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getCustomField('12345', $options);
    }

    /**
     * Test updateCustomField calls client with correct parameters.
     */
    public function testUpdateCustomField(): void
    {
        $data = ['name' => 'Updated Name', 'description' => 'New description'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'custom_fields/12345',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateCustomField('12345', $data);
    }

    /**
     * Test deleteCustomField calls client with correct parameters.
     */
    public function testDeleteCustomField(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'custom_fields/12345', [], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->deleteCustomField('12345');
    }

    /**
     * Test getCustomFieldsForWorkspace calls client with correct parameters.
     */
    public function testGetCustomFieldsForWorkspace(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/custom_fields', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getCustomFieldsForWorkspace('12345');
    }

    /**
     * Test getCustomFieldsForWorkspace with options.
     */
    public function testGetCustomFieldsForWorkspaceWithOptions(): void
    {
        $options = ['opt_fields' => 'name,resource_subtype', 'limit' => 50];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/custom_fields', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getCustomFieldsForWorkspace('12345', $options);
    }

    /**
     * Test createEnumOption calls client with correct parameters.
     */
    public function testCreateEnumOption(): void
    {
        $data = ['name' => 'Critical', 'color' => 'red'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'custom_fields/12345/enum_options',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createEnumOption('12345', $data);
    }

    /**
     * Test createEnumOption with positioning.
     */
    public function testCreateEnumOptionWithPositioning(): void
    {
        $data = ['name' => 'Medium', 'color' => 'yellow', 'insert_after' => 'opt123'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'custom_fields/12345/enum_options',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createEnumOption('12345', $data);
    }

    /**
     * Test reorderEnumOption calls client with correct parameters.
     */
    public function testReorderEnumOption(): void
    {
        $data = ['enum_option' => 'opt123', 'before_enum_option' => 'opt456'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'custom_fields/12345/enum_options/insert',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->reorderEnumOption('12345', $data);
    }

    /**
     * Test reorderEnumOption with after_enum_option.
     */
    public function testReorderEnumOptionWithAfter(): void
    {
        $data = ['enum_option' => 'opt123', 'after_enum_option' => 'opt789'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'custom_fields/12345/enum_options/insert',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->reorderEnumOption('12345', $data);
    }

    /**
     * Test updateEnumOption calls client with correct parameters.
     */
    public function testUpdateEnumOption(): void
    {
        $data = ['name' => 'Updated Option', 'color' => 'green'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'custom_fields/12345/enum_options/67890',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateEnumOption('12345', '67890', $data);
    }

    /**
     * Test updateEnumOption with enabled flag.
     */
    public function testUpdateEnumOptionWithEnabled(): void
    {
        $data = ['enabled' => false];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'custom_fields/12345/enum_options/67890',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateEnumOption('12345', '67890', $data);
    }

    /**
     * Test getCustomFieldSettingsForProject calls client with correct parameters.
     */
    public function testGetCustomFieldSettingsForProject(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'projects/12345/custom_field_settings', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getCustomFieldSettingsForProject('12345');
    }

    /**
     * Test getCustomFieldSettingsForPortfolio calls client with correct parameters.
     */
    public function testGetCustomFieldSettingsForPortfolio(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'portfolios/12345/custom_field_settings', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getCustomFieldSettingsForPortfolio('12345');
    }

    /**
     * Test methods with custom response type.
     */
    public function testGetCustomFieldWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'custom_fields/12345', ['query' => []], AsanaApiClient::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getCustomField('12345', [], AsanaApiClient::RESPONSE_FULL);
    }
}
