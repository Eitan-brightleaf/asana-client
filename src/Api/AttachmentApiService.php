<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;
use RuntimeException;

class AttachmentApiService
{
    /**
     * An HTTP client instance configured to interact with the Asana API.
     *
     * This property stores an instance of AsanaApiClient which handles all HTTP communication
     * with the Asana API endpoints. It provides authenticated access to API resources and
     * manages request/response handling.
     */
    private AsanaApiClient $client;

    /**
     * Constructor for initializing the service with an Asana API client.
     *
     * Sets up the service instance using the provided Asana API client.
     *
     * @param AsanaApiClient $client The Asana API client instance used to interact with the Asana API.
     *
     * @return void
     */
    public function __construct(AsanaApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get an attachment
     *
     * GET /attachments/{attachment_gid}
     *
     * Returns the full record for a single attachment.
     * Attachments are files uploaded to objects in Asana (such as tasks).
     *
     * API Documentation: https://developers.asana.com/reference/getattachment
     *
     * @param string $attachmentGid The unique global ID of the attachment to retrieve.
     *                              This identifier can be found in the attachment URL or
     *                              returned from attachment-related API endpoints.
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body
     *
     * @return array If $fullResponse is true, returns complete response array including:
     *               - status: HTTP status code
     *               - reason: Status reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns just the attachment record containing:
     *               - gid: Attachment's unique identifier
     *               - resource_type: Always "attachment"
     *               - name: Attachment filename
     *               - created_at: Timestamp when the attachment was created
     *               - download_url: URL where the attachment can be downloaded
     *               - host: The service hosting the attachment (e.g., "asana", "dropbox", etc.)
     *               - parent: The parent object the attachment is attached to
     *               Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If the API request fails due to invalid attachment GID,
     *                          insufficient permissions, network issues, or rate limiting
     */
    public function getAttachment(string $attachmentGid, array $options = [], bool $fullResponse = false): array
    {
        return $this->client->request('GET', "attachments/$attachmentGid", ['query' => $options], $fullResponse);
    }

    /**
     * Delete an attachment
     *
     * DELETE /attachments/{attachment_gid}
     *
     * Deletes a specific, existing attachment. Only the owner of the attachment
     * can delete it.
     *
     * API Documentation: https://developers.asana.com/reference/deleteattachment
     *
     * @param string $attachmentGid The unique global ID of the attachment to delete.
     *                              This identifier can be found in the attachment URL or
     *                              returned from attachment-related API endpoints.
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body
     *
     * @return array If $fullResponse is true, returns complete response array including:
     *               - status: HTTP status code
     *               - reason: Status reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns empty data object containing:
     *               - data: An empty JSON object {}
     *
     * @throws AsanaApiException If the API request fails due to:
     *                          - Invalid attachment GID
     *                          - Insufficient permissions to delete the attachment
     *                          - Network connectivity issues
     *                          - Rate limiting
     */
    public function deleteAttachment(string $attachmentGid, bool $fullResponse = false): array
    {
        return $this->client->request('DELETE', "attachments/$attachmentGid", [], $fullResponse);
    }

    /**
     * Get attachments from an object
     *
     * GET /attachments
     *
     * Returns the compact records for all attachments on the object.
     *
     * API Documentation: https://developers.asana.com/reference/getattachmentsforobject
     *
     * @param string $parentGid The unique global ID of the parent object
     *                          for which to get attachments.
     * @param array $options Optional parameters to customize the request:
     *                      - limit (int): Maximum number of items to return (1-100)
     *                      - offset (string): Offset token for pagination
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body
     *
     * @return array If $fullResponse is true, returns complete response array including:
     *               - status: HTTP status code
     *               - reason: Status reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns list of attachments containing at minimum:
     *               - gid: Attachment's unique identifier
     *               - resource_type: Always "attachment"
     *               - name: Attachment filename
     *               Additional fields if specified in opt_fields
     *
     * @throws AsanaApiException If invalid parent GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getAttachmentsForObject(string $parentGid, array $options = [], bool $fullResponse = false): array
    {
        $queryParams = array_merge(['parent' => $parentGid], $options);
        return $this->client->request('GET', 'attachments', ['query' => $queryParams], $fullResponse);
    }

    /**
     * Upload an attachment
     *
     * POST /attachments
     *
     * Upload an attachment to a task, project, or story. This method is useful when you have
     * a file on disk and can provide the file path. If you have the file contents in memory,
     * consider using `uploadAttachmentFromContents` instead.
     *
     * API Documentation: https://developers.asana.com/reference/createattachmentforobject
     *
     * @param string $parentGid The GID of the parent object (task, project, or story) to attach the file to.
     * @param string $filePath The local file path of the file to upload.
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body
     *
     * @return array If $fullResponse is true, returns complete response array including:
     *               - status: HTTP status code
     *               - reason: Status reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns the created attachment data including:
     *               - gid: Attachment's unique identifier
     *               - resource_type: Always "attachment"
     *               - name: Attachment filename
     *               - created_at: Timestamp when the attachment was created
     *               - download_url: URL where the attachment can be downloaded
     *               - host: The service hosting the attachment (always "asana" for uploaded files)
     *               - parent: The parent object the attachment is attached to
     *               Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If the file doesn't exist, is too large, invalid parent GID,
     *                          insufficient permissions, or network issues occur
     * @throws RuntimeException If the file does not exist or is not readable
     */
    public function uploadAttachment(
        string $parentGid,
        string $filePath,
        array $options = [],
        bool $fullResponse = false
    ): array {
        // Check if file exists and is readable before attempting to open
        if (!is_readable($filePath)) {
            throw new RuntimeException("File at '$filePath' does not exist or is not readable");
        }

        // Create multipart form data options for the request
        $multipartOptions = [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => fopen($filePath, 'r'),
                    'filename' => basename($filePath)
                ],
                [
                    'name' => 'parent',
                    'contents' => $parentGid
                ]
            ]
        ];

        // Add query parameters if options are provided
        if (!empty($options)) {
            $multipartOptions['query'] = $options;
        }

        return $this->client->request('POST', 'attachments', $multipartOptions, $fullResponse);
    }
    /**
     * Upload an attachment from file contents
     *
     * POST /attachments
     *
     * Upload an attachment to a task, project, or story using file contents.
     * This method is useful when you have the file content in memory rather than on disk.
     *
     * API Documentation: https://developers.asana.com/reference/createattachmentforobject
     *
     * @param string $parentGid The GID of the parent object (task, project, or story) to attach the file to.
     * @param string $fileContents The contents of the file to upload.
     * @param string $fileName The name to give to the uploaded file.
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body
     *
     * @return array If $fullResponse is true, returns complete response array including:
     *               - status: HTTP status code
     *               - reason: Status reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body
     *               - raw_body: Raw response body string
     *               - request: Original request details
     *               If $fullResponse is false, returns the created attachment data
     *               (see uploadAttachment for details on return structure)
     *
     * @throws AsanaApiException If the file is too large, invalid parent GID, insufficient permissions, or network issues occur
     * @throws RuntimeException If the stream cannot be created or written to or if the stream is not writable
     */
    public function uploadAttachmentFromContents(
        string $parentGid,
        string $fileContents,
        string $fileName,
        array $options = [],
        bool $fullResponse = false
    ): array {
        // Create a temporary stream with the file contents
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw new RuntimeException('Failed to create temporary stream');
        }
        // Write the file contents to the stream
        // Check if the stream is writable
        if (stream_get_meta_data($stream)['mode'] !== 'r+') {
            fclose($stream);
            throw new RuntimeException('Stream is not writable');
        }
        // Write the file contents to the stream and check if fwrite was successful
        if (fwrite($stream, $fileContents) === false) {
            fclose($stream);
            throw new RuntimeException('Failed to write to temporary stream');
        }
        // Rewind the stream to the beginning for reading
        rewind($stream);

        // Create multipart form data options for the request
        $multipartOptions = [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => $stream,
                    'filename' => $fileName
                ],
                [
                    'name'     => 'parent',
                    'contents' => $parentGid
                ]
            ]
        ];

        // Add query parameters if options are provided
        if (!empty($options)) {
            $multipartOptions['query'] = $options;
        }
        
        // Make the request to upload the attachment
        return $this->client->request('POST', 'attachments', $multipartOptions, $fullResponse);
        // Don't have to close the stream b/c Guzzle does it.
    }
}
