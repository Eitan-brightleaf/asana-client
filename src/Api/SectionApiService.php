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
     * GET /sections/{section_gid}
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
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If invalid section GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getSection(string $sectionGid, array $options = [], int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request('GET', "sections/$sectionGid", ['query' => $options], $responseType);
    }

    /**
     * Update a section
     *
     * PUT /sections/{section_gid}
     *
     * Updates the properties of a section. Only the fields provided in the data block will be updated;
     * any unspecified fields will remain unchanged.
     *
     * API Documentation: https://developers.asana.com/reference/updatesection
     *
     * @param string $sectionGid The unique global ID of the section to update.
     * @param array $data The properties of the section to update.
     * @param array $options Optional parameters to customize the request.
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If invalid section GID provided, malformed data,
     *                          insufficient permissions, or network issues occur
     */
    public function updateSection(
        string $sectionGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request(
            'PUT',
            "sections/$sectionGid",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Delete a section
     *
     * DELETE /sections/{section_gid}
     *
     * Deletes a section from a project. This operation is only possible for
     * sections in board or list projects that have the opt-in layout feature enabled.
     * This does not delete tasks within the section - they will be moved to other sections
     * in the project based on the project's configuration.
     *
     * API Documentation: https://developers.asana.com/reference/deletesection
     *
     * @param string $sectionGid The unique global ID of the section to delete.
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If the API request fails due to:
     *                          - Invalid section GID
     *                          - Section is in a project type that doesn't support section deletion
     *                          - Insufficient permissions to delete the section
     *                          - Network connectivity issues
     *                          - Rate limiting
     */
    public function deleteSection(string $sectionGid, int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request('DELETE', "sections/$sectionGid", [], $responseType);
    }

    /**
     * Get sections in a project
     *
     * GET /projects/{project_gid}/sections
     *
     * Returns the compact records for all sections in the specified project.
     * Sections represent an organizational unit within a project and help group tasks.
     *
     * API Documentation: https://developers.asana.com/reference/getsectionsforproject
     *
     * @param string $projectGid The unique global ID of the project for which to get sections.
     * @param array $options Optional parameters to customize the request.
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If invalid project GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getSectionsForProject(string $projectGid, array $options = [], int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request('GET', "projects/$projectGid/sections", ['query' => $options], $responseType);
    }

    /**
     * Create a section in a project
     *
     * POST /projects/{project_gid}/sections
     *
     * Creates a new section in a project. Returns the full record of the newly created section.
     * Sections can be created in board projects and list projects with the layout feature enabled.
     *
     * API Documentation: https://developers.asana.com/reference/createsectionforproject
     *
     * @param string $projectGid The unique global ID of the project in which to create the section.
     * @param array $data Data for creating the section.
     * @param array $options Optional parameters to customize the request.
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If invalid project GID provided, project doesn't support sections,
     *                          malformed data, insufficient permissions, or network issues occur
     */
    public function createSectionForProject(
        string $projectGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request(
            'POST',
            "projects/$projectGid/sections",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Add task to section
     *
     * POST /sections/{section_gid}/addTask
     *
     * Adds a task to a specific section. This will remove the task from other sections
     * of the project.
     *
     * API Documentation: https://developers.asana.com/reference/addtaskforsection
     *
     * @param string $sectionGid The unique global ID of the section to add the task to.
     * @param array $data Data for adding a task to the section.
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If the task doesn't exist, section doesn't exist, insufficient permissions,
     *                          task already in section, or network issues occur
     */
    public function addTaskToSection(string $sectionGid, array $data, int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request(
            'POST',
            "sections/$sectionGid/addTask",
            ['json' => ['data' => $data]],
            $responseType
        );
    }

    /**
     * Move or insert sections
     *
     * POST /projects/{project_gid}/sections/insert
     *
     * Move sections or insert a section in a project. This endpoint allows you to reorder sections or
     * insert a section at a specific index in the project.
     *
     * API Documentation: https://developers.asana.com/reference/insertsectionforproject
     *
     * @param string $projectGid The unique global ID of the project in which to reorder sections.
     * @param array $data Data for inserting/reordering sections.
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If the project doesn't exist, sections don't exist, invalid positioning,
     *                          insufficient permissions, or network issues occur
     */
    public function insertSectionForProject(string $projectGid, array $data, int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request(
            'POST',
            "projects/$projectGid/sections/insert",
            ['json' => ['data' => $data]],
            $responseType
        );
    }
}
