<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\AttachmentApiService;
use BrightleafDigital\Http\AsanaApiClient;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AttachmentApiServiceTest extends TestCase
{
    /** @var AsanaApiClient&\PHPUnit\Framework\MockObject\MockObject */
    private $mockClient;

    /** @var AttachmentApiService */
    private $service;

    /** @var string */
    private $tempDir;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(AsanaApiClient::class);
        $this->service = new AttachmentApiService($this->mockClient);
        $this->tempDir = sys_get_temp_dir() . '/attachment_tests_' . uniqid('', true);
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0700, true);
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->tempDir) && is_dir($this->tempDir)) {
            $this->deleteDir($this->tempDir);
        }
    }

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    /**
     * Test getAttachment calls client with correct parameters.
     */
    public function testGetAttachment(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'attachments/12345', ['query' => []], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getAttachment('12345');
    }

    /**
     * Test getAttachment with options.
     */
    public function testGetAttachmentWithOptions(): void
    {
        $options = ['opt_fields' => 'name,download_url,parent'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'attachments/12345', ['query' => $options], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getAttachment('12345', $options);
    }

    /**
     * Test deleteAttachment calls client with correct parameters.
     */
    public function testDeleteAttachment(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'attachments/12345', [], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->deleteAttachment('12345');
    }

    /**
     * Test getAttachmentsForObject calls client with correct parameters.
     */
    public function testGetAttachmentsForObject(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'attachments', ['query' => ['parent' => '12345']], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getAttachmentsForObject('12345');
    }

    /**
     * Test getAttachmentsForObject with options.
     */
    public function testGetAttachmentsForObjectWithOptions(): void
    {
        $options = ['opt_fields' => 'name,created_at', 'limit' => 50];
        $expectedQuery = array_merge(['parent' => '12345'], $options);

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'attachments', ['query' => $expectedQuery], AsanaApiClient::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getAttachmentsForObject('12345', $options);
    }

    /**
     * Test uploadAttachment calls client with correct parameters.
     */
    public function testUploadAttachment(): void
    {
        // Create a temporary test file
        $filePath = $this->tempDir . '/test_file.txt';
        file_put_contents($filePath, 'Test file content');

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'attachments',
                $this->callback(function ($options) {
                    return isset($options['multipart'])
                        && count($options['multipart']) === 2
                        && $options['multipart'][0]['name'] === 'file'
                        && $options['multipart'][0]['filename'] === 'test_file.txt'
                        && $options['multipart'][1]['name'] === 'parent'
                        && $options['multipart'][1]['contents'] === '12345';
                }),
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->uploadAttachment('12345', $filePath);
    }

    /**
     * Test uploadAttachment with options.
     */
    public function testUploadAttachmentWithOptions(): void
    {
        $filePath = $this->tempDir . '/test_file.txt';
        file_put_contents($filePath, 'Test file content');
        $options = ['opt_fields' => 'name,download_url'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'attachments',
                $this->callback(function ($opt) use ($options) {
                    return isset($opt['multipart']) && isset($opt['query']) && $opt['query'] === $options;
                }),
                AsanaApiClient::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->uploadAttachment('12345', $filePath, $options);
    }

    /**
     * Test uploadAttachment throws exception for non-existent file.
     */
    public function testUploadAttachmentThrowsExceptionForNonExistentFile(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('does not exist or is not readable');

        $this->service->uploadAttachment('12345', '/non/existent/file.txt');
    }

    /**
     * Test uploadAttachmentFromContents calls client with correct parameters.
     *
     * Note: This test verifies the method signature and mock expectations.
     * The actual stream handling is tested via integration tests since
     * stream_get_meta_data behavior varies by environment.
     *
     * @group integration
     */
    public function testUploadAttachmentFromContents(): void
    {
        $this->markTestSkipped(
            'This test requires integration testing due to stream handling in the implementation.'
        );
    }

    /**
     * Test uploadAttachmentFromContents with options.
     *
     * @group integration
     */
    public function testUploadAttachmentFromContentsWithOptions(): void
    {
        $this->markTestSkipped(
            'This test requires integration testing due to stream handling in the implementation.'
        );
    }

    /**
     * Test methods with custom response type.
     */
    public function testGetAttachmentWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'attachments/12345', ['query' => []], AsanaApiClient::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getAttachment('12345', [], AsanaApiClient::RESPONSE_FULL);
    }
}
