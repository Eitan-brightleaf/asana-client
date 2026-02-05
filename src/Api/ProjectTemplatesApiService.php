<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Utils\ValidationTrait;
use InvalidArgumentException;

class ProjectTemplatesApiService
{
    use ValidationTrait;

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
     * Get a project template
     *
     * GET /project_templates/{project_template_gid}
     *
     * Returns the full record for a single project template.
     *
     * API Documentation: https://developers.asana.com/reference/getprojecttemplate
     *
     * @param string $projectTemplateGid The unique global ID of the project template.
     *                                   Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include
     *                        (e.g., "name,description,owner,team,public")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing project template data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the template details including:
     *                 - gid: Unique identifier of the project template
     *                 - resource_type: Always "project_template"
     *                 - name: Name of the project template
     *                 - description: Description of the template
     *                 - owner: Object containing the owner details
     *                 - team: Object containing the team details
     *                 - public: Whether the template is public
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getProjectTemplate(
        string $projectTemplateGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($projectTemplateGid, 'Project Template GID');

        return $this->client->request(
            'GET',
            "project_templates/$projectTemplateGid",
            ['query' => $options],
            $responseType
        );
    }

    /**
     * Get multiple project templates
     *
     * GET /project_templates
     *
     * Returns the compact project template records for all project templates
     * in the given workspace or team.
     *
     * API Documentation: https://developers.asana.com/reference/getprojecttemplates
     *
     * @param array $options Optional parameters to customize the request:
     *                      Filtering parameters:
     *                      - workspace (string): GID of a workspace to filter templates from
     *                      - team (string): GID of a team to filter templates from
     *                      Pagination parameters:
     *                      - limit (int): Maximum number of templates to return. Default is 20, max is 100
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing project template data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of project templates
     *
     * @throws AsanaApiException If insufficient permissions, network issues, or rate limiting occurs
     */
    public function getProjectTemplates(
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request(
            'GET',
            'project_templates',
            ['query' => $options],
            $responseType
        );
    }

    /**
     * Get project templates for a team
     *
     * GET /teams/{team_gid}/project_templates
     *
     * Returns the compact project template records for all project templates
     * in the given team.
     *
     * API Documentation: https://developers.asana.com/reference/getprojecttemplatesforteam
     *
     * @param string $teamGid The unique global ID of the team.
     *                        Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      Pagination parameters:
     *                      - limit (int): Maximum number of templates to return. Default is 20, max is 100
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include
     *                        (e.g., "name,description,owner,team")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing project template data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of project templates for the team
     *
     * @throws AsanaApiException If invalid team GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getProjectTemplatesForTeam(
        string $teamGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($teamGid, 'Team GID');

        return $this->client->request(
            'GET',
            "teams/$teamGid/project_templates",
            ['query' => $options],
            $responseType
        );
    }

    /**
     * Instantiate a project from a project template
     *
     * POST /project_templates/{project_template_gid}/instantiateProject
     *
     * Creates and returns a job that will asynchronously handle the project instantiation.
     * The job will create a new project based on the specified template.
     *
     * API Documentation: https://developers.asana.com/reference/instantiateproject
     *
     * @param string $projectTemplateGid The unique global ID of the project template.
     *                                   Example: "12345"
     * @param array $data Data for instantiating the project. Supported fields include:
     *                    Required:
     *                    - name (string): Name of the new project.
     *                      Example: "Q1 Product Launch"
     *                    Optional:
     *                    - team (string): GID of the team the project belongs to
     *                    - public (bool): Whether the new project is public
     *                    - is_strict (bool): If true, the project will be created in strict mode
     *                    - requested_dates (array): Array of date overrides for the template
     *                    Example: ["name" => "Q1 Product Launch"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing job data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the job details including:
     *                 - gid: Unique identifier of the job
     *                 - resource_type: Always "job"
     *                 - status: Status of the job
     *                 - new_project: Object containing the new project details
     *
     * @throws InvalidArgumentException If the project template GID is invalid or name is missing
     * @throws AsanaApiException If the template doesn't exist, insufficient permissions,
     *                          or network issues occur
     */
    public function instantiateProject(
        string $projectTemplateGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($projectTemplateGid, 'Project Template GID');
        $this->validateRequiredFields($data, ['name'], 'project instantiation');

        return $this->client->request(
            'POST',
            "project_templates/$projectTemplateGid/instantiateProject",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Delete a project template
     *
     * DELETE /project_templates/{project_template_gid}
     *
     * Deletes the specified project template. This action is permanent
     * and cannot be undone.
     *
     * API Documentation: https://developers.asana.com/reference/deleteprojecttemplate
     *
     * @param string $projectTemplateGid The unique global ID of the project template.
     *                                   Example: "12345"
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response
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
     *               - Complete decoded JSON response including empty data object
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object (empty JSON object {}) indicating successful deletion
     *
     * @throws AsanaApiException If the API request fails due to:
     *                          - Invalid project template GID
     *                          - Template not found
     *                          - Insufficient permissions to delete the template
     *                          - Network connectivity issues
     *                          - Rate limiting
     */
    public function deleteProjectTemplate(
        string $projectTemplateGid,
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($projectTemplateGid, 'Project Template GID');

        return $this->client->request(
            'DELETE',
            "project_templates/$projectTemplateGid",
            [],
            $responseType
        );
    }
}
