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
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing attachment data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the attachment details
     *
     * @throws AsanaApiException If the API request fails due to invalid attachment GID,
     *                          insufficient permissions, network issues, or rate limiting
     */
    public function getAttachment(
        string $attachmentGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request('GET', "attachments/$attachmentGid", ['query' => $options], $responseType);
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
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body (empty data object)
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object (empty JSON object {})
     *
     * @throws AsanaApiException If the API request fails due to:
     *                          - Invalid attachment GID
     *                          - Insufficient permissions to delete the attachment
     *                          - Network connectivity issues
     *                          - Rate limiting
     */
    public function deleteAttachment(string $attachmentGid, int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request('DELETE', "attachments/$attachmentGid", [], $responseType);
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
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing attachment list
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of attachments
     *
     * @throws AsanaApiException If invalid parent GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getAttachmentsForObject(
        string $parentGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $queryParams = array_merge(['parent' => $parentGid], $options);
        return $this->client->request('GET', 'attachments', ['query' => $queryParams], $responseType);
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
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing attachment data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the created attachment
     *
     * @throws AsanaApiException If the file doesn't exist, is too large, invalid parent GID,
     *                          insufficient permissions, or network issues occur
     * @throws RuntimeException If the file does not exist or is not readable
     */
    public function uploadAttachment(
        string $parentGid,
        string $filePath,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
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

        return $this->client->request('POST', 'attachments', $multipartOptions, $responseType);
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
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing attachment data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the created attachment
     *
     * @throws AsanaApiException If the file is too large, invalid parent GID, or network issues occur, etc.
     * @throws RuntimeException If the stream cannot be created or written to or if the stream is not writable
     */
    public function uploadAttachmentFromContents(
        string $parentGid,
        string $fileContents,
        string $fileName,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
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
        return $this->client->request('POST', 'attachments', $multipartOptions, $responseType);
        // Don't have to close the stream b/c Guzzle does it.
    }
}
