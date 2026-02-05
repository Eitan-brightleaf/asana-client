<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\ProjectTemplatesApiService;
use BrightleafDigital\Http\AsanaApiClient;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;

class ProjectTemplatesApiServiceTest extends TestCase
{
    /** @var AsanaApiClient&\PHPUnit\Framework\MockObject\MockObject */
    private $mockClient;

    /** @var ProjectTemplatesApiService */
    private $service;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(AsanaApiClient::class);
        $this->service = new ProjectTemplatesApiService($this->mockClient);
    }

    // ── getProjectTemplate ──────────────────────────────────────────────

    /**
     * Test getProjectTemplate calls client with correct parameters.
     */
    public function testGetProjectTemplate(): void
    {
        $expectedResponse = [
            'gid' => '12345',
            'resource_type' => 'project_template',
            'name' => 'Sprint Template',
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'project_templates/12345',
                ['query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getProjectTemplate('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getProjectTemplate with options.
     */
    public function testGetProjectTemplateWithOptions(): void
    {
        $options = ['opt_fields' => 'name,description,owner,team'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'project_templates/12345',
                ['query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getProjectTemplate('12345', $options);
    }

    /**
     * Test getProjectTemplate with custom response type.
     */
    public function testGetProjectTemplateWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'project_templates/12345',
                ['query' => []],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getProjectTemplate(
            '12345',
            [],
            AsanaApiClient::RESPONSE_FULL
        );
    }

    /**
     * Test getProjectTemplate throws exception for empty GID.
     */
    public function testGetProjectTemplateThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Project Template GID must be a non-empty string.'
        );

        $this->service->getProjectTemplate('');
    }

    /**
     * Test getProjectTemplate throws exception for non-numeric GID.
     */
    public function testGetProjectTemplateThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Project Template GID must be a numeric string.'
        );

        $this->service->getProjectTemplate('abc');
    }

    // ── getProjectTemplates ─────────────────────────────────────────────

    /**
     * Test getProjectTemplates calls client with correct parameters.
     */
    public function testGetProjectTemplates(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'name' => 'Template A'],
            ['gid' => '222', 'name' => 'Template B'],
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'project_templates',
                ['query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getProjectTemplates();

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getProjectTemplates with workspace filter.
     */
    public function testGetProjectTemplatesWithWorkspaceFilter(): void
    {
        $options = ['workspace' => '12345'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'project_templates',
                ['query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getProjectTemplates($options);
    }

    /**
     * Test getProjectTemplates with team filter.
     */
    public function testGetProjectTemplatesWithTeamFilter(): void
    {
        $options = ['team' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'project_templates',
                ['query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getProjectTemplates($options);
    }

    /**
     * Test getProjectTemplates with custom response type.
     */
    public function testGetProjectTemplatesWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'project_templates',
                ['query' => []],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getProjectTemplates([], AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test getProjectTemplates with multiple options.
     */
    public function testGetProjectTemplatesWithMultipleOptions(): void
    {
        $options = [
            'workspace' => '12345',
            'opt_fields' => 'name,description',
            'limit' => 50,
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'project_templates',
                ['query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getProjectTemplates($options);
    }

    // ── getProjectTemplatesForTeam ──────────────────────────────────────

    /**
     * Test getProjectTemplatesForTeam calls client with correct parameters.
     */
    public function testGetProjectTemplatesForTeam(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'name' => 'Team Template A'],
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'teams/12345/project_templates',
                ['query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getProjectTemplatesForTeam('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getProjectTemplatesForTeam with options.
     */
    public function testGetProjectTemplatesForTeamWithOptions(): void
    {
        $options = ['opt_fields' => 'name,description,owner'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'teams/12345/project_templates',
                ['query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getProjectTemplatesForTeam('12345', $options);
    }

    /**
     * Test getProjectTemplatesForTeam with custom response type.
     */
    public function testGetProjectTemplatesForTeamWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'teams/12345/project_templates',
                ['query' => []],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getProjectTemplatesForTeam(
            '12345',
            [],
            AsanaApiClient::RESPONSE_FULL
        );
    }

    /**
     * Test getProjectTemplatesForTeam throws exception for empty GID.
     */
    public function testGetProjectTemplatesForTeamThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Team GID must be a non-empty string.'
        );

        $this->service->getProjectTemplatesForTeam('');
    }

    /**
     * Test getProjectTemplatesForTeam throws exception for non-numeric GID.
     */
    public function testGetProjectTemplatesForTeamThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Team GID must be a numeric string.'
        );

        $this->service->getProjectTemplatesForTeam('abc');
    }

    // ── instantiateProject ──────────────────────────────────────────────

    /**
     * Test instantiateProject calls client with correct parameters.
     */
    public function testInstantiateProject(): void
    {
        $data = ['name' => 'Q1 Product Launch'];
        $expectedResponse = [
            'gid' => '99999',
            'resource_type' => 'job',
            'status' => 'queued',
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'project_templates/12345/instantiateProject',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->instantiateProject('12345', $data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test instantiateProject with options.
     */
    public function testInstantiateProjectWithOptions(): void
    {
        $data = ['name' => 'Q1 Product Launch'];
        $options = ['opt_fields' => 'status,new_project'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'project_templates/12345/instantiateProject',
                ['json' => ['data' => $data], 'query' => $options],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->instantiateProject('12345', $data, $options);
    }

    /**
     * Test instantiateProject with optional fields.
     */
    public function testInstantiateProjectWithOptionalFields(): void
    {
        $data = [
            'name' => 'Q1 Product Launch',
            'team' => '67890',
            'public' => true,
            'is_strict' => false,
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'project_templates/12345/instantiateProject',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->instantiateProject('12345', $data);
    }

    /**
     * Test instantiateProject with custom response type.
     */
    public function testInstantiateProjectWithCustomResponseType(): void
    {
        $data = ['name' => 'Q1 Product Launch'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'project_templates/12345/instantiateProject',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->instantiateProject(
            '12345',
            $data,
            [],
            AsanaApiClient::RESPONSE_FULL
        );
    }

    /**
     * Test instantiateProject throws exception for empty GID.
     */
    public function testInstantiateProjectThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Project Template GID must be a non-empty string.'
        );

        $this->service->instantiateProject(
            '',
            ['name' => 'Test']
        );
    }

    /**
     * Test instantiateProject throws exception for non-numeric GID.
     */
    public function testInstantiateProjectThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Project Template GID must be a numeric string.'
        );

        $this->service->instantiateProject(
            'abc',
            ['name' => 'Test']
        );
    }

    /**
     * Test instantiateProject throws exception when name is missing.
     */
    public function testInstantiateProjectThrowsExceptionForMissingName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Missing required field(s) for project instantiation: name'
        );

        $this->service->instantiateProject('12345', []);
    }

    /**
     * Test instantiateProject throws exception when name is missing with other fields.
     */
    public function testInstantiateProjectThrowsExceptionForMissingNameWithOtherFields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Missing required field(s) for project instantiation: name'
        );

        $this->service->instantiateProject(
            '12345',
            ['team' => '67890', 'public' => true]
        );
    }

    // ── deleteProjectTemplate ───────────────────────────────────────────

    /**
     * Test deleteProjectTemplate calls client with correct parameters.
     */
    public function testDeleteProjectTemplate(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'DELETE',
                'project_templates/12345',
                [],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $result = $this->service->deleteProjectTemplate('12345');

        $this->assertSame([], $result);
    }

    /**
     * Test deleteProjectTemplate with custom response type.
     */
    public function testDeleteProjectTemplateWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'DELETE',
                'project_templates/12345',
                [],
                AsanaApiClient::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->deleteProjectTemplate(
            '12345',
            AsanaApiClient::RESPONSE_FULL
        );
    }

    /**
     * Test deleteProjectTemplate throws exception for empty GID.
     */
    public function testDeleteProjectTemplateThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Project Template GID must be a non-empty string.'
        );

        $this->service->deleteProjectTemplate('');
    }

    /**
     * Test deleteProjectTemplate throws exception for non-numeric GID.
     */
    public function testDeleteProjectTemplateThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Project Template GID must be a numeric string.'
        );

        $this->service->deleteProjectTemplate('abc');
    }
}
