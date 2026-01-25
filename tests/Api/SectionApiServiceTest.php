<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\SectionApiService;
use BrightleafDigital\Http\AsanaApiClient;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;

class SectionApiServiceTest extends TestCase
{
    /** @var AsanaApiClient&\PHPUnit\Framework\MockObject\MockObject */
    private $mockClient;

    /** @var SectionApiService */
    private $service;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(AsanaApiClient::class);
        $this->service = new SectionApiService($this->mockClient);
    }

    /**
     * Test getSection calls client with correct parameters.
     */
    public function testGetSection(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'sections/12345', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getSection('12345');
    }

    /**
     * Test getSection with options.
     */
    public function testGetSectionWithOptions(): void
    {
        $options = ['opt_fields' => 'name,project,created_at'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'sections/12345', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getSection('12345', $options);
    }

    /**
     * Test updateSection calls client with correct parameters.
     */
    public function testUpdateSection(): void
    {
        $data = ['name' => 'Updated Section'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'sections/12345',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateSection('12345', $data);
    }

    /**
     * Test deleteSection calls client with correct parameters.
     */
    public function testDeleteSection(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'sections/12345', [], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->deleteSection('12345');
    }

    /**
     * Test getSectionsForProject calls client with correct parameters.
     */
    public function testGetSectionsForProject(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'projects/67890/sections', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getSectionsForProject('67890');
    }

    /**
     * Test createSectionForProject calls client with correct parameters.
     */
    public function testCreateSectionForProject(): void
    {
        $data = ['name' => 'New Section'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'projects/67890/sections',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createSectionForProject('67890', $data);
    }

    /**
     * Test createSectionForProject with insert_before.
     */
    public function testCreateSectionForProjectWithInsertBefore(): void
    {
        $data = ['name' => 'New Section', 'insert_before' => '99999'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'projects/67890/sections',
                ['json' => ['data' => $data], 'query' => []],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createSectionForProject('67890', $data);
    }

    /**
     * Test addTaskToSection calls client with correct parameters.
     */
    public function testAddTaskToSection(): void
    {
        $data = ['task' => '11111'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'sections/12345/addTask',
                ['json' => ['data' => $data]],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addTaskToSection('12345', $data);
    }

    /**
     * Test addTaskToSection with insert positioning.
     */
    public function testAddTaskToSectionWithPositioning(): void
    {
        $data = ['task' => '11111', 'insert_after' => '22222'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'sections/12345/addTask',
                ['json' => ['data' => $data]],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addTaskToSection('12345', $data);
    }

    /**
     * Test insertSectionForProject calls client with correct parameters.
     */
    public function testInsertSectionForProject(): void
    {
        $data = ['section' => '12345', 'after_section' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'projects/99999/sections/insert',
                ['json' => ['data' => $data]],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->insertSectionForProject('99999', $data);
    }

    /**
     * Test insertSectionForProject with before_section.
     */
    public function testInsertSectionForProjectWithBeforeSection(): void
    {
        $data = ['section' => '12345', 'before_section' => '67890'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'projects/99999/sections/insert',
                ['json' => ['data' => $data]],
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->insertSectionForProject('99999', $data);
    }

    /**
     * Test methods with custom response type.
     */
    public function testGetSectionWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'sections/12345', ['query' => []], AsanaApiClient::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getSection('12345', [], AsanaApiClient::RESPONSE_FULL);
    }
}
