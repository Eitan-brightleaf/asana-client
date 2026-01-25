<?php

namespace BrightleafDigital\Tests\Auth;

use BrightleafDigital\Auth\Scopes;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ScopesTest extends TestCase
{
    /**
     * Test all scope constants are defined and are strings.
     */
    public function testAllScopesAreStrings(): void
    {
        $reflection = new ReflectionClass(Scopes::class);
        $constants = $reflection->getConstants();

        $this->assertNotEmpty($constants, 'Scopes class should have constants defined');

        foreach ($constants as $name => $value) {
            $this->assertIsString($value, "Constant {$name} should be a string");
            $this->assertNotEmpty($value, "Constant {$name} should not be empty");
        }
    }

    /**
     * Test attachment scope constants.
     */
    public function testAttachmentScopes(): void
    {
        $this->assertSame('attachments:delete', Scopes::ATTACHMENTS_DELETE);
        $this->assertSame('attachments:read', Scopes::ATTACHMENTS_READ);
        $this->assertSame('attachments:write', Scopes::ATTACHMENTS_WRITE);
    }

    /**
     * Test custom fields scope constants.
     */
    public function testCustomFieldsScopes(): void
    {
        $this->assertSame('custom_fields:read', Scopes::CUSTOM_FIELDS_READ);
        $this->assertSame('custom_fields:write', Scopes::CUSTOM_FIELDS_WRITE);
    }

    /**
     * Test goals scope constant.
     */
    public function testGoalsScopes(): void
    {
        $this->assertSame('goals:read', Scopes::GOALS_READ);
    }

    /**
     * Test portfolios scope constants.
     */
    public function testPortfoliosScopes(): void
    {
        $this->assertSame('portfolios:read', Scopes::PORTFOLIOS_READ);
        $this->assertSame('portfolios:write', Scopes::PORTFOLIOS_WRITE);
    }

    /**
     * Test project templates scope constant.
     */
    public function testProjectTemplatesScopes(): void
    {
        $this->assertSame('project_templates:read', Scopes::PROJECT_TEMPLATES_READ);
    }

    /**
     * Test projects scope constants.
     */
    public function testProjectsScopes(): void
    {
        $this->assertSame('projects:delete', Scopes::PROJECTS_DELETE);
        $this->assertSame('projects:read', Scopes::PROJECTS_READ);
        $this->assertSame('projects:write', Scopes::PROJECTS_WRITE);
    }

    /**
     * Test stories scope constants.
     */
    public function testStoriesScopes(): void
    {
        $this->assertSame('stories:read', Scopes::STORIES_READ);
        $this->assertSame('stories:write', Scopes::STORIES_WRITE);
    }

    /**
     * Test tags scope constants.
     */
    public function testTagsScopes(): void
    {
        $this->assertSame('tags:read', Scopes::TAGS_READ);
        $this->assertSame('tags:write', Scopes::TAGS_WRITE);
    }

    /**
     * Test task templates scope constant.
     */
    public function testTaskTemplatesScopes(): void
    {
        $this->assertSame('task_templates:read', Scopes::TASK_TEMPLATES_READ);
    }

    /**
     * Test tasks scope constants.
     */
    public function testTasksScopes(): void
    {
        $this->assertSame('tasks:delete', Scopes::TASKS_DELETE);
        $this->assertSame('tasks:read', Scopes::TASKS_READ);
        $this->assertSame('tasks:write', Scopes::TASKS_WRITE);
    }

    /**
     * Test team memberships scope constant.
     */
    public function testTeamMembershipsScopes(): void
    {
        $this->assertSame('team_memberships:read', Scopes::TEAM_MEMBERSHIPS_READ);
    }

    /**
     * Test teams scope constant.
     */
    public function testTeamsScopes(): void
    {
        $this->assertSame('teams:read', Scopes::TEAMS_READ);
    }

    /**
     * Test time tracking scope constant.
     */
    public function testTimeTrackingScopes(): void
    {
        $this->assertSame('time_tracking_entries:read', Scopes::TIME_TRACKING_READ);
    }

    /**
     * Test timesheet approval statuses scope constants.
     */
    public function testTimesheetApprovalStatusesScopes(): void
    {
        $this->assertSame('timesheet_approval_statuses:read', Scopes::TIMESHEET_APPROVAL_STATUSES_READ);
        $this->assertSame('timesheet_approval_statuses:write', Scopes::TIMESHEET_APPROVAL_STATUSES_WRITE);
    }

    /**
     * Test workspace typeahead scope constant.
     */
    public function testWorkspaceTypeaheadScopes(): void
    {
        $this->assertSame('workspace.typeahead:read', Scopes::WORKSPACE_TYPEAHEAD_READ);
    }

    /**
     * Test users scope constant.
     */
    public function testUsersScopes(): void
    {
        $this->assertSame('users:read', Scopes::USERS_READ);
    }

    /**
     * Test webhooks scope constants.
     */
    public function testWebhooksScopes(): void
    {
        $this->assertSame('webhooks:delete', Scopes::WEBHOOKS_DELETE);
        $this->assertSame('webhooks:read', Scopes::WEBHOOKS_READ);
        $this->assertSame('webhooks:write', Scopes::WEBHOOKS_WRITE);
    }

    /**
     * Test workspaces scope constant.
     */
    public function testWorkspacesScopes(): void
    {
        $this->assertSame('workspaces:read', Scopes::WORKSPACES_READ);
    }

    /**
     * Test OpenID Connect scope constants.
     */
    public function testOpenIdConnectScopes(): void
    {
        $this->assertSame('openid', Scopes::OPENID);
        $this->assertSame('email', Scopes::EMAIL);
        $this->assertSame('profile', Scopes::PROFILE);
    }

    /**
     * Test scope format follows the pattern resource:action.
     */
    public function testScopeFormatPattern(): void
    {
        $reflection = new ReflectionClass(Scopes::class);
        $constants = $reflection->getConstants();

        $specialScopes = ['openid', 'email', 'profile'];

        foreach ($constants as $name => $value) {
            if (in_array($value, $specialScopes, true)) {
                // OpenID scopes don't follow the resource:action pattern
                continue;
            }

            $this->assertMatchesRegularExpression(
                '/^[\w.]+:(read|write|delete)$/',
                $value,
                "Scope {$name} ({$value}) should match pattern resource:action"
            );
        }
    }

    /**
     * Test that read, write, and delete scopes exist for main resources.
     */
    public function testMainResourcesHaveReadWriteDeleteScopes(): void
    {
        // Resources that should have all three
        $fullResources = ['attachments', 'projects', 'tasks', 'webhooks'];

        foreach ($fullResources as $resource) {
            $readConst = strtoupper($resource) . '_READ';
            $writeConst = strtoupper($resource) . '_WRITE';
            $deleteConst = strtoupper($resource) . '_DELETE';

            $this->assertTrue(
                defined(Scopes::class . '::' . $readConst),
                "Scope constant {$readConst} should exist"
            );
            $this->assertTrue(
                defined(Scopes::class . '::' . $writeConst),
                "Scope constant {$writeConst} should exist"
            );
            $this->assertTrue(
                defined(Scopes::class . '::' . $deleteConst),
                "Scope constant {$deleteConst} should exist"
            );
        }
    }

    /**
     * Test scopes can be combined into array for OAuth request.
     */
    public function testScopesCanBeCombined(): void
    {
        $scopes = [
            Scopes::TASKS_READ,
            Scopes::TASKS_WRITE,
            Scopes::PROJECTS_READ,
        ];

        $scopeString = implode(' ', $scopes);

        $this->assertSame('tasks:read tasks:write projects:read', $scopeString);
    }

    /**
     * Test no duplicate scope values exist.
     */
    public function testNoDuplicateScopeValues(): void
    {
        $reflection = new ReflectionClass(Scopes::class);
        $constants = $reflection->getConstants();

        $values = array_values($constants);
        $uniqueValues = array_unique($values);

        $this->assertCount(
            count($values),
            $uniqueValues,
            'All scope values should be unique'
        );
    }
}
