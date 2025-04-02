<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;

class SectionApiService
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
     * Get a section
     *
     * Returns the complete record for a single section.
     * Sections are used to divide projects into smaller parts.
     *
     * API Documentation: https://developers.asana.com/reference/getsection
     *
     * @param string $sectionGid The unique global ID of the section to retrieve.
     *                           This identifier can be found in the section URL or
     *                           returned from section-related API endpoints.
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse If true, returns the complete response data including headers,
     *                          status code, and raw response body. If false, returns just the
     *                          response data.
     *
     * @return array When $fullResponse is true, returns complete response array containing:
     *               - status: HTTP status code
     *               - reason: Status reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body with section data
     *               - raw_body: Raw response body string
     *               - request: Original request data
     *               When $fullResponse is false, returns section data containing at minimum:
     *               - gid: Section's unique identifier
     *               - name: Section name
     *               - resource_type: Always "section"
     *               Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid section GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getSection(string $sectionGid, array $options = [], bool $fullResponse = false): array
    {
        return $this->client->request('GET', "sections/$sectionGid", ['query' => $options], $fullResponse);
    }

    /**
     * Update a section
     *
     * Updates the properties of a section. Only the fields provided in the data block will be updated;
     * any unspecified fields will remain unchanged.
     *
     * API Documentation: https://developers.asana.com/reference/updatesection
     *
     * @param string $sectionGid The unique global ID of the section to update.
     *                           This identifier can be found in the section URL or
     *                           returned from section-related API endpoints.
     * @param array $data The properties of the section to update. Can include:
     *                    - name (string): Name of the section
     *                    Example: ["name" => "Updated Section Name"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse If true, returns the complete response data including headers,
     *                          status code, and raw response body. If false, returns just the
     *                          response data.
     *
     * @return array When $fullResponse is true, returns complete response array containing:
     *               - status: HTTP status code
     *               - reason: Status reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body with updated section data
     *               - raw_body: Raw response body string
     *               - request: Original request data
     *               When $fullResponse is false, returns updated section data including:
     *               - gid: Section's unique identifier
     *               - name: Updated section name
     *               - resource_type: Always "section"
     *               Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid section GID provided, malformed data,
     *                          insufficient permissions, or network issues occur
     */
    public function updateSection(
        string $sectionGid,
        array $data,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'PUT',
            "sections/$sectionGid",
            ['json' => ['data' => $data], 'query' => $options],
            $fullResponse
        );
    }

    /**
     * Delete a section
     *
     * Deletes a section from a project. This operation is only possible for
     * sections in board or list projects that have the opt-in layout feature enabled.
     * This does not delete tasks within the section - they will be moved to other sections
     * in the project based on the project's configuration.
     *
     * API Documentation: https://developers.asana.com/reference/deletesection
     *
     * @param string $sectionGid The unique global ID of the section to delete.
     *                           This identifier can be found in the section URL or
     *                           returned from section-related API endpoints.
     * @param bool $fullResponse If true, returns the complete response data including headers,
     *                          status code, and raw response body. If false, returns just the
     *                          response data.
     *
     * @return array When $fullResponse is true, returns complete response array containing:
     *               - status: HTTP status code
     *               - reason: Status reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body
     *               - raw_body: Raw response body string
     *               - request: Original request data
     *               When $fullResponse is false, returns empty data object containing:
     *               - data: An empty JSON object {}
     *
     * @throws AsanaApiException If the API request fails due to:
     *                          - Invalid section GID
     *                          - Section is in a project type that doesn't support section deletion
     *                          - Insufficient permissions to delete the section
     *                          - Network connectivity issues
     *                          - Rate limiting
     */
    public function deleteSection(string $sectionGid, bool $fullResponse = false): array
    {
        return $this->client->request('DELETE', "sections/$sectionGid", [], $fullResponse);
    }

    /**
     * Get sections in a project
     *
     * Returns the compact records for all sections in the specified project.
     * Sections represent an organizational unit within a project and help group tasks.
     *
     * API Documentation: https://developers.asana.com/reference/getsectionsforproject
     *
     * @param string $projectGid The unique global ID of the project for which to get sections.
     *                           This identifier can be found in the project URL or
     *                           returned from project-related API endpoints.
     * @param array $options Optional parameters to customize the request:
     *                      - limit (int): Results to return per page (1-100)
     *                      - offset (string): Pagination offset token
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse If true, returns the complete response data including headers,
     *                          status code, and raw response body. If false, returns just the
     *                          response data.
     *
     * @return array When $fullResponse is true, returns complete response array containing:
     *               - status: HTTP status code
     *               - reason: Status reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body with sections list
     *               - raw_body: Raw response body string
     *               - request: Original request data
     *               When $fullResponse is false, returns list of sections containing at minimum:
     *               - gid: Section's unique identifier
     *               - name: Section name
     *               - resource_type: Always "section"
     *               Additional fields if specified in opt_fields
     *
     * @throws AsanaApiException If invalid project GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getSectionsForProject(string $projectGid, array $options = [], bool $fullResponse = false): array
    {
        return $this->client->request('GET', "projects/$projectGid/sections", ['query' => $options], $fullResponse);
    }

    /**
     * Create a section in a project
     *
     * Creates a new section in a project. Returns the full record of the newly created section.
     * Sections can be created in board projects and list projects with the layout feature enabled.
     *
     * API Documentation: https://developers.asana.com/reference/createsectionforproject
     *
     * @param string $projectGid The unique global ID of the project in which to create the section.
     *                           This identifier can be found in the project URL or
     *                           returned from project-related API endpoints.
     * @param array $data Data for creating the section. Supported fields include:
     *                    Required:
     *                    - name (string): Name of the section
     *                    Optional:
     *                    - insert_before (string): Section GID before which the new section should be inserted
     *                    - insert_after (string): Section GID after which the new section should be inserted
     *
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse If true, returns the complete response data including headers,
     *                          status code, and raw response body. If false, returns just the
     *                          response data.
     *
     * @return array When $fullResponse is true, returns complete response array containing:
     *               - status: HTTP status code
     *               - reason: Status reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body with created section data
     *               - raw_body: Raw response body string
     *               - request: Original request data
     *               When $fullResponse is false, returns created section data including:
     *               - gid: Section's unique identifier
     *               - name: Section name
     *               - resource_type: Always "section"
     *               Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid project GID provided, project doesn't support sections,
     *                          malformed data, insufficient permissions, or network issues occur
     */
    public function createSectionForProject(
        string $projectGid,
        array $data,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'POST',
            "projects/$projectGid/sections",
            ['json' => ['data' => $data], 'query' => $options],
            $fullResponse
        );
    }

    /**
     * Add task to section
     *
     * Adds a task to a specific section. This will remove the task from other sections
     * of the project.
     *
     * API Documentation: https://developers.asana.com/reference/addtaskforsection
     *
     * @param string $sectionGid The unique global ID of the section to add the task to.
     *                           This identifier can be found in the section URL or
     *                           returned from section-related API endpoints.
     * @param array $data Data for adding a task to the section. Required fields:
     *                    - task (string): The task GID to add to the section
     *                    Optional:
     *                    - insert_before (string): Insert the task before this task GID within the section
     *                    - insert_after (string): Insert the task after this task GID within the section
     * @param bool $fullResponse If true, returns the complete response data including headers,
     *                          status code, and raw response body. If false, returns just the
     *                          response data.
     *
     * @return array When $fullResponse is true, returns complete response array containing:
     *               - status: HTTP status code
     *               - reason: Status reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body
     *               - raw_body: Raw response body string
     *               - request: Original request data
     *               When $fullResponse is false, returns empty data object indicating success:
     *               - data: An empty JSON object {}
     *
     * @throws AsanaApiException If the task doesn't exist, section doesn't exist, insufficient permissions,
     *                          task already in section, or network issues occur
     */
    public function addTaskToSection(string $sectionGid, array $data, bool $fullResponse = false): array
    {
        return $this->client->request(
            'POST',
            "sections/$sectionGid/addTask",
            ['json' => ['data' => $data]],
            $fullResponse
        );
    }

    /**
     * Move or insert sections
     *
     * Move sections or insert a section in a project. This endpoint allows you to reorder sections or
     * insert a section at a specific index in the project.
     *
     * API Documentation: https://developers.asana.com/reference/insertsectionforproject
     *
     * @param string $projectGid The unique global ID of the project in which to reorder sections.
     *                           This identifier can be found in the project URL or
     *                           returned from project-related API endpoints.
     * @param array $data Data for inserting/reordering sections. Required fields:
     *                    - section (string): The GID of the section to move or insert
     *                    Optional (one of the following is required):
     *                    - before_section (string): Insert this section before the specified section GID
     *                    - after_section (string): Insert this section after the specified section GID
     * @param bool $fullResponse If true, returns the complete response data including headers,
     *                          status code, and raw response body. If false, returns just the
     *                          response data.
     *
     * @return array When $fullResponse is true, returns complete response array containing:
     *               - status: HTTP status code
     *               - reason: Status reason phrase
     *               - headers: Response headers
     *               - body: Decoded response body
     *               - raw_body: Raw response body string
     *               - request: Original request data
     *               When $fullResponse is false, returns empty data object indicating success:
     *               - data: An empty JSON object {}
     *
     * @throws AsanaApiException If the project doesn't exist, sections don't exist, invalid positioning,
     *                          insufficient permissions, or network issues occur
     */
    public function insertSectionForProject(string $projectGid, array $data, bool $fullResponse = false): array
    {
        return $this->client->request(
            'POST',
            "projects/$projectGid/sections/insert",
            ['json' => ['data' => $data]],
            $fullResponse
        );
    }
}
