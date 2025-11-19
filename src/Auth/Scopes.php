<?php

namespace BrightleafDigital\Auth;

class Scopes
{
    // 🔹 Attachments

    /**
     * DELETE /attachments/{attachment_gid}
     */
    public const ATTACHMENTS_DELETE = 'attachments:delete';

    /**
     * - GET /attachments/{attachment_gid}
     * - GET /attachments
     */
    public const ATTACHMENTS_READ = 'attachments:read';

    /**
     * POST /attachments
     */
    public const ATTACHMENTS_WRITE = 'attachments:write';

    // 🔹 Custom Fields
    /**
     * - GET /custom_fields/{custom_field_gid}
     * - GET /workspaces/{workspace_gid}/custom_fields
     */
    public const CUSTOM_FIELDS_READ = 'custom_fields:read';
    /**
     * - POST /custom_fields
     * - PUT /custom_fields/{custom_field_gid}
     * - POST /custom_fields/{custom_field_gid}/enum_options
     * - POST /custom_fields/{custom_field_gid}/enum_options/insert
     * - PUT /enum_options/{enum_option_gid}
     */
    public const CUSTOM_FIELDS_WRITE = 'custom_fields:write';

    /**
     * 🔹 Goals
     * - GET /goals/{goal_gid}
     * - GET /goals
     * - GET /goals/{goal_gid}/parentGoals
     */
    public const GOALS_READ = 'goals:read';

    // 🔹 Portfolios
    /**
     * - GET /portfolios
     * - GET /portfolios/{portfolio_gid}
     * - GET /portfolios/{portfolio_gid}/items
     */
    public const PORTFOLIOS_READ = 'portfolios:read';

    /**
     * - POST /portfolios
     * - PUT /portfolios/{portfolio_gid}
     * - POST /portfolios/{portfolio_gid}/addItem
     * - POST /portfolios/{portfolio_gid}/removeItem
     * - POST /portfolios/{portfolio_gid}/addCustomFieldSetting
     * - POST /portfolios/{portfolio_gid}/removeCustomFieldSetting
     */
    public const PORTFOLIOS_WRITE = 'portfolios:write';

    /**
     * 🔹 Project Templates
     * - GET /project_templates/{project_template_gid}
     * - GET /project_templates
     * - GET /teams/{team_gid}/project_templates
     */
    public const PROJECT_TEMPLATES_READ = 'project_templates:read';

    // 🔹 Projects
    /**
     * DELETE /projects/{project_gid}
     */
    public const PROJECTS_DELETE = 'projects:delete';

    /**
     * - GET /projects
     * - GET /projects/{project_gid}
     * - GET /tasks/{task_gid}/projects
     * - GET /teams/{team_gid}/projects
     * - GET /workspaces/{workspace_gid}/projects
     * - GET /projects/{project_gid}/task_counts
     */
    public const PROJECTS_READ = 'projects:read';

    /**
     * - POST /project_templates/{project_template_gid}/instantiateProject
     * - POST /projects
     * - PUT /projects/{project_gid}
     * - POST /projects/{project_gid}/duplicate
     * - POST /teams/{team_gid}/projects
     * - POST /workspaces/{workspace_gid}/projects
     * - POST /projects/{project_gid}/addCustomFieldSetting
     * - POST /projects/{project_gid}/removeCustomFieldSetting
     */
    public const PROJECTS_WRITE = 'projects:write';

    // 🔹 Stories
    /**
     * - GET /stories/{story_gid}
     * - GET /tasks/{task_gid}/stories
     */
    public const STORIES_READ = 'stories:read';

    /**
     * - PUT /stories/{story_gid}
     * - POST /tasks/{task_gid}/stories
     */
    public const STORIES_WRITE = 'stories:write';

    // 🔹 Tags
    /**
     * - GET /tags
     * - GET /tags/{tag_gid}
     * - GET /tasks/{task_gid}/tags
     * - GET /workspaces/{workspace_gid}/tags
     */
    public const TAGS_READ = 'tags:read';
    /**
     * - POST /tags
     * - PUT /tags/{tag_gid}
     * - POST /workspaces/{workspace_gid}/tags
     */
    public const TAGS_WRITE = 'tags:write';

    // 🔹 Task templates
    /**
     * - GET /task_templates
     * - GET /task_templates/{task_template_gid}
     */
    public const TASK_TEMPLATES_READ = 'task_templates:read';

    // 🔹 Tasks
    /**
     * DELETE /tasks/{task_gid}
     */
    public const TASKS_DELETE = 'tasks:delete';

    /**
     * - GET /tasks
     * - GET /tasks/{task_gid}
     * - GET /projects/{project_gid}/tasks
     * - GET /sections/{section_gid}/tasks
     * - GET /tags/{tag_gid}/tasks
     * - GET /user_task_lists/{user_task_list_gid}/tasks
     * - GET /tasks/{task_gid}/subtasks
     * - GET /tasks/{task_gid}/dependencies
     * - GET /tasks/{task_gid}/dependents
     * - GET /workspaces/{workspace_gid}/tasks/custom_id/{custom_id}
     * - GET /workspaces/{workspace_gid}/tasks/search
     * - GET /user_task_lists/{user_task_list_gid}
     * - GET /users/{user_gid}/user_task_list
     */
    public const TASKS_READ = 'tasks:read';

    /**
     * - POST /sections/{section_gid}/addTask
     * - POST /tasks
     * - PUT /tasks/{task_gid}
     * - POST /tasks/{task_gid}/duplicate
     * - POST /tasks/{task_gid}/subtasks
     * - POST /tasks/{task_gid}/setParent
     * - POST /tasks/{task_gid}/addDependencies
     * - POST /tasks/{task_gid}/removeDependencies
     * - POST /tasks/{task_gid}/addDependents
     * - POST /tasks/{task_gid}/removeDependents
     * - POST /tasks/{task_gid}/addProject
     * - POST /tasks/{task_gid}/removeProject
     * - POST /tasks/{task_gid}/addTag
     * - POST /tasks/{task_gid}/removeTag
     * - POST /tasks/{task_gid}/addFollowers
     * - POST /tasks/{task_gid}/removeFollowers
     */
    public const TASKS_WRITE = 'tasks:write';

    /**
     * 🔹 Team Memberships
     * - GET /team_memberships/{team_membership_gid}
     * - GET /team_memberships
     * - GET /teams/{team_gid}/team_memberships
     * - GET /users/{user_gid}/team_memberships
     */
    public const TEAM_MEMBERSHIPS_READ = 'team_memberships:read';

    /**
     * 🔹 Teams
     * - GET /teams/{team_gid}
     * - GET /workspaces/{workspace_gid}/teams
     * - GET /users/{user_gid}/teams
     */
    public const TEAMS_READ = 'teams:read';

    /**
     * 🔹Time tracking entries
     * - GET /tasks/{task_gid}/time_tracking_entries
     * - GET /time_tracking_entries/{time_tracking_entry_gid}
     * - GET /time_tracking_entries
     */
    public const TIME_TRACKING_READ = 'time_tracking_entries:read';

    /**
     * 🔹 Typeahead
     * GET /workspaces/{workspace_gid}/typeahead
     */
    public const WORKSPACE_TYPEAHEAD_READ = 'workspace.typeahead:read';

    /**
     * 🔹 Users
     * - GET /users
     * - GET /users/{user_gid}
     * - GET /users/{user_gid}/favorites
     * - GET /teams/{team_gid}/users
     * - GET /workspaces/{workspace_gid}/users
     */
    public const USERS_READ = 'users:read';

    // 🔹 Webhooks
    /**
     * DELETE /webhooks/{webhook_gid}
     */
    public const WEBHOOKS_DELETE = 'webhooks:delete';

    /**
     * - GET /webhooks
     * - GET /webhooks/{webhook_gid}
     */
    public const WEBHOOKS_READ = 'webhooks:read';
    /**
     * - POST /webhooks
     * - PUT /webhooks/{webhook_gid}
     */
    public const WEBHOOKS_WRITE = 'webhooks:write';

    /**
     * 🔹 Workspaces
     * - GET /workspaces
     * - GET /workspaces/{workspace_gid}
     */
    public const WORKSPACES_READ = 'workspaces:read';

    // 🔹 OpenID Connect
    /**
     * Provides access to OpenID Connect ID tokens and the OpenID Connect user info endpoint.
     */
    public const OPENID = 'openid';

    /**
     * Provides access to the user's email through the OpenID Connect user info endpoint.
     */
    public const EMAIL = 'email';

    /**
     * Provides access to the user's name and profile photo through the OpenID Connect user info endpoint.
     */
    public const PROFILE = 'profile';
}
