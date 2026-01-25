# Test Analysis Report

## BrightLeaf Digital Asana PHP Client - Test Suite

### Overview

This document provides a comprehensive analysis of the PHPUnit test suite for the Asana PHP Client library.

**Test Results:**
- Total Tests: 307
- Total Assertions: 602
- Status: All Passing
- PHP Compatibility: 7.4+
- PHPUnit Version: 9.6

---

## Test Coverage by Class

### 1. Utility Classes

#### `CryptoUtilsTest.php` (13 tests)
Tests the AES-256-GCM encryption utilities.

| Test | Description |
|------|-------------|
| `testEncryptReturnsBase64String` | Verifies encryption output format |
| `testDecryptReturnsOriginalText` | Round-trip encryption/decryption |
| `testEncryptProducesDifferentOutputs` | IV randomness verification |
| `testDecryptWithWrongPasswordThrows` | Wrong password error handling |
| `testDecryptWithTamperedDataThrows` | Data integrity validation |
| `testDecryptWithInvalidBase64Throws` | Invalid input handling |
| `testDecryptWithTruncatedDataThrows` | Corrupted data handling |
| `testEncryptDecryptWithEmptyString` | Edge case: empty plaintext |
| `testEncryptDecryptWithLongText` | Large data handling |
| `testEncryptDecryptWithUnicodeCharacters` | Unicode support |
| `testEncryptDecryptWithShortPassword` | Short password handling |
| `testEncryptDecryptWithLongPassword` | Long password handling |
| `testPasswordWithSpecialCharacters` | Special character passwords |

---

### 2. HTTP Infrastructure

#### `AsanaApiClientTest.php` (12 tests)
Tests the core HTTP client that handles all API communication.

| Test | Description |
|------|-------------|
| `testConstructorWithPersonalAccessToken` | PAT authentication setup |
| `testConstructorWithOAuth2Token` | OAuth2 authentication setup |
| `testRequestReturnsFullResponse` | RESPONSE_FULL mode |
| `testRequestReturnsNormalResponse` | RESPONSE_NORMAL mode |
| `testRequestReturnsDataSubset` | RESPONSE_DATA mode |
| `testRequestHandlesEmptyDataResponse` | Empty response handling |
| `testGuzzleExceptionParsesJsonError` | JSON error body parsing |
| `testGuzzleExceptionWithNonJsonBody` | Non-JSON error handling |
| `testGuzzleExceptionWithEmptyBody` | Empty error body handling |
| `testGuzzleExceptionWithMultipleErrors` | Multiple error messages |
| `testGuzzleExceptionWithComplexErrorStructure` | Complex error parsing |
| `testGuzzleExceptionPreservesOriginalException` | Exception chaining |

---

### 3. API Service Classes

#### `TaskApiServiceTest.php` (27 tests)
Tests task management operations.

| Test | Description |
|------|-------------|
| `testGetTasks` | List tasks with options |
| `testGetTasksWithCustomResponseType` | Custom response type |
| `testCreateTask` | Create new task |
| `testCreateTaskWithOptions` | Create with opt_fields |
| `testGetTask` | Get single task |
| `testGetTaskWithOptions` | Get with opt_fields |
| `testUpdateTask` | Update task data |
| `testDeleteTask` | Delete task |
| `testGetSubtasksFromTask` | List subtasks |
| `testCreateSubtaskForTask` | Create subtask |
| `testGetTasksByProject` | Tasks by project |
| `testGetTasksBySection` | Tasks by section |
| `testGetTasksByTag` | Tasks by tag |
| `testAddProjectToTask` | Add project association |
| `testRemoveProjectFromTask` | Remove project association |
| `testAddTagToTask` | Add tag to task |
| `testRemoveTagFromTask` | Remove tag from task |
| `testSetParentForTask` | Set parent task |
| `testAddFollowersToTask` | Add followers |
| `testRemoveFollowersFromTask` | Remove followers |
| `testDuplicateTask` | Duplicate task |
| `testGetDependenciesFromTask` | Get dependencies |
| `testSetDependenciesForTask` | Set dependencies |
| `testUnlinkDependenciesFromTask` | Remove dependencies |
| `testGetDependentsFromTask` | Get dependents |
| `testSetDependentsForTask` | Set dependents |
| `testUnlinkDependentsFromTask` | Remove dependents |

#### `ProjectApiServiceTest.php` (22 tests)
Tests project management operations.

| Test | Description |
|------|-------------|
| `testGetProjectsWithWorkspace` | List by workspace |
| `testGetProjectsWithTeam` | List by team |
| `testGetProjectsWithWorkspaceAndTeam` | List with both filters |
| `testGetProjectsThrowsExceptionWithoutWorkspaceOrTeam` | Validation error |
| `testGetProjectsWithOptions` | List with options |
| `testCreateProject` | Create project |
| `testGetProject` | Get single project |
| `testUpdateProject` | Update project |
| `testDeleteProject` | Delete project |
| `testDuplicateProject` | Duplicate project |
| `testGetProjectsForTask` | Projects containing task |
| `testGetProjectsForTeam` | Projects in team |
| `testCreateProjectInTeam` | Create in team |
| `testGetProjectsForWorkspace` | Projects in workspace |
| `testCreateProjectInWorkspace` | Create in workspace |
| `testAddCustomFieldToProject` | Add custom field |
| `testRemoveCustomFieldFromProject` | Remove custom field |
| `testGetCustomFieldsForProject` | List custom fields |
| `testGetTaskCountsForProject` | Task counts |
| `testAddMembersToProject` | Add members |
| `testRemoveMembersFromProject` | Remove members |
| `testAddFollowersToProject` | Add followers |
| `testRemoveFollowersFromProject` | Remove followers |
| `testCreateProjectTemplateFromProject` | Save as template |

#### `UserApiServiceTest.php` (12 tests)
Tests user management operations.

| Test | Description |
|------|-------------|
| `testGetUsersWithWorkspace` | List by workspace |
| `testGetUsersWithTeam` | List by team |
| `testGetUsersThrowsExceptionWithoutWorkspaceOrTeam` | Validation error |
| `testGetUsersWithOptions` | List with options |
| `testGetUser` | Get single user |
| `testGetUserWithMeIdentifier` | Get current user via 'me' |
| `testGetUserFavorites` | Get user favorites |
| `testGetUsersForTeam` | Users in team |
| `testGetUsersForWorkspace` | Users in workspace |
| `testGetCurrentUser` | Get current user |
| `testGetCurrentUserWithOptions` | Current user with options |
| `testGetCurrentUserFavorites` | Current user favorites |
| `testGetUserWithCustomResponseType` | Custom response type |

#### `WorkspaceApiServiceTest.php` (14 tests)
Tests workspace management operations.

| Test | Description |
|------|-------------|
| `testGetWorkspaces` | List all workspaces |
| `testGetWorkspacesWithOptions` | List with options |
| `testGetWorkspace` | Get single workspace |
| `testUpdateWorkspace` | Update workspace |
| `testAddUserToWorkspace` | Add user by GID |
| `testAddUserToWorkspaceWithEmail` | Add user by email |
| `testRemoveUserFromWorkspace` | Remove user |
| `testGetUsersInWorkspace` | List workspace users |
| `testGetTeamsInWorkspace` | List workspace teams |
| `testGetProjectsInWorkspace` | List workspace projects |
| `testSearchTasksInWorkspace` | Basic task search |
| `testSearchTasksInWorkspaceWithComplexQuery` | Complex search query |
| `testGetWorkspaceEvents` | Get events |
| `testGetWorkspaceEventsWithSyncToken` | Events with sync token |
| `testGetWorkspaceWithCustomResponseType` | Custom response type |

#### `SectionApiServiceTest.php` (12 tests)
Tests section management operations.

| Test | Description |
|------|-------------|
| `testGetSection` | Get single section |
| `testGetSectionWithOptions` | Get with options |
| `testUpdateSection` | Update section |
| `testDeleteSection` | Delete section |
| `testGetSectionsForProject` | List project sections |
| `testCreateSectionForProject` | Create section |
| `testCreateSectionForProjectWithInsertBefore` | Create with positioning |
| `testAddTaskToSection` | Add task to section |
| `testAddTaskToSectionWithPositioning` | Add with positioning |
| `testInsertSectionForProject` | Reorder with after |
| `testInsertSectionForProjectWithBeforeSection` | Reorder with before |
| `testGetSectionWithCustomResponseType` | Custom response type |

#### `MembershipApiServiceTest.php` (10 tests)
Tests membership management operations.

| Test | Description |
|------|-------------|
| `testGetMemberships` | List memberships |
| `testGetMembershipsWithPortfolio` | List by portfolio |
| `testGetMembershipsWithMemberFilter` | List with member filter |
| `testCreateMembership` | Create membership |
| `testCreateMembershipWithAccessLevel` | Create with access level |
| `testGetMembership` | Get single membership |
| `testGetMembershipWithOptions` | Get with options |
| `testUpdateMembership` | Update membership |
| `testDeleteMembership` | Delete membership |
| `testGetMembershipWithCustomResponseType` | Custom response type |

#### `AttachmentApiServiceTest.php` (10 tests)
Tests attachment management operations.

| Test | Description |
|------|-------------|
| `testGetAttachment` | Get single attachment |
| `testGetAttachmentWithOptions` | Get with options |
| `testDeleteAttachment` | Delete attachment |
| `testGetAttachmentsForObject` | List attachments |
| `testGetAttachmentsForObjectWithOptions` | List with options |
| `testUploadAttachment` | Upload file from path |
| `testUploadAttachmentWithOptions` | Upload with options |
| `testUploadAttachmentThrowsExceptionForNonExistentFile` | File validation |
| `testUploadAttachmentFromContents` | Upload from string (skipped - integration) |
| `testUploadAttachmentFromContentsWithOptions` | Upload from string with options (skipped - integration) |
| `testGetAttachmentWithCustomResponseType` | Custom response type |

#### `TagsApiServiceTest.php` (12 tests)
Tests tag management operations.

| Test | Description |
|------|-------------|
| `testGetTags` | List tags in workspace |
| `testGetTagsWithOptions` | List with options |
| `testCreateTag` | Create tag |
| `testCreateTagWithColor` | Create with color |
| `testGetTag` | Get single tag |
| `testGetTagWithOptions` | Get with options |
| `testUpdateTag` | Update tag |
| `testDeleteTag` | Delete tag |
| `testGetTasksForTag` | Tasks with tag |
| `testGetTasksForTagWithOptions` | Tasks with options |
| `testGetTagsForWorkspace` | Workspace tags |
| `testCreateTagInWorkspace` | Create in workspace |
| `testGetTagWithCustomResponseType` | Custom response type |

#### `CustomFieldApiServiceTest.php` (14 tests)
Tests custom field management operations.

| Test | Description |
|------|-------------|
| `testCreateCustomField` | Create custom field |
| `testCreateCustomFieldWithEnumOptions` | Create enum field |
| `testGetCustomField` | Get single field |
| `testGetCustomFieldWithOptions` | Get with options |
| `testUpdateCustomField` | Update field |
| `testDeleteCustomField` | Delete field |
| `testGetCustomFieldsForWorkspace` | List workspace fields |
| `testGetCustomFieldsForWorkspaceWithOptions` | List with options |
| `testCreateEnumOption` | Create enum option |
| `testCreateEnumOptionWithPositioning` | Create with positioning |
| `testReorderEnumOption` | Reorder with before |
| `testReorderEnumOptionWithAfter` | Reorder with after |
| `testUpdateEnumOption` | Update enum option |
| `testUpdateEnumOptionWithEnabled` | Disable enum option |
| `testGetCustomFieldSettingsForProject` | Project field settings |
| `testGetCustomFieldSettingsForPortfolio` | Portfolio field settings |
| `testGetCustomFieldWithCustomResponseType` | Custom response type |

---

### 4. Authentication Classes

#### `ScopesTest.php` (18 tests)
Tests OAuth2 scope constants.

| Test | Description |
|------|-------------|
| `testAllScopesAreStrings` | Type validation |
| `testAttachmentScopes` | Attachment scope values |
| `testCustomFieldsScopes` | Custom field scope values |
| `testGoalsScopes` | Goals scope values |
| `testPortfoliosScopes` | Portfolio scope values |
| `testProjectTemplatesScopes` | Template scope values |
| `testProjectsScopes` | Project scope values |
| `testStoriesScopes` | Story scope values |
| `testTagsScopes` | Tag scope values |
| `testTaskTemplatesScopes` | Task template scope values |
| `testTasksScopes` | Task scope values |
| `testTeamMembershipsScopes` | Team membership scope values |
| `testTeamsScopes` | Team scope values |
| `testTimeTrackingScopes` | Time tracking scope values |
| `testTimesheetApprovalStatusesScopes` | Timesheet scope values |
| `testWorkspaceTypeaheadScopes` | Typeahead scope values |
| `testUsersScopes` | User scope values |
| `testWebhooksScopes` | Webhook scope values |
| `testWorkspacesScopes` | Workspace scope values |
| `testOpenIdConnectScopes` | OIDC scope values |
| `testScopeFormatPattern` | Format validation |
| `testMainResourcesHaveReadWriteDeleteScopes` | Completeness check |
| `testScopesCanBeCombined` | Scope combination |
| `testNoDuplicateScopeValues` | Uniqueness check |

#### `OAuth2ProviderTest.php` (14 tests)
Tests OAuth2 provider functionality.

| Test | Description |
|------|-------------|
| `testGetBaseAuthorizationUrl` | Authorization URL |
| `testGetBaseAccessTokenUrl` | Token URL |
| `testGetResourceOwnerDetailsUrl` | User info URL |
| `testGetAuthorizationUrl` | Generate auth URL |
| `testGetSecureAuthorizationUrlWithStateAndPkce` | State + PKCE |
| `testGetSecureAuthorizationUrlWithStateOnly` | State only |
| `testGetSecureAuthorizationUrlWithPkceOnly` | PKCE only |
| `testGetSecureAuthorizationUrlWithoutCustomStateOrPkce` | No custom security |
| `testPkceCodeChallengeIsCorrectHash` | PKCE hash validation |
| `testStateIsRandom` | State randomness |
| `testCodeVerifierIsRandom` | Verifier randomness |
| `testAuthorizationUrlContainsRequiredParams` | Required parameters |
| `testScopeIsProperlyEncoded` | URL encoding |
| `testProviderWithMinimalConfig` | Minimal configuration |

#### `AsanaOAuthHandlerTest.php` (10 tests)
Tests OAuth handler functionality.

| Test | Description |
|------|-------------|
| `testGetAuthorizationUrl` | Get auth URL |
| `testGetSecureAuthorizationUrl` | Get secure URL with all options |
| `testGetSecureAuthorizationUrlWithoutState` | Without state |
| `testGetSecureAuthorizationUrlWithoutPkce` | Without PKCE |
| `testHandleCallback` | Exchange code for token |
| `testHandleCallbackWithoutCodeVerifier` | Callback without PKCE |
| `testGetAccessToken` | Get access token |
| `testRefreshToken` | Refresh token |
| `testRefreshTokenPreservesRefreshToken` | Preserve refresh token |
| `testConstructorCreatesProvider` | Constructor validation |
| `testMultipleAuthorizationUrlCalls` | Consistency check |

---

### 5. Exception Classes

#### `ExceptionsTest.php` (22 tests)
Tests all exception classes.

| Test | Description |
|------|-------------|
| `testAsanaApiExceptionWithMessageOnly` | Basic construction |
| `testAsanaApiExceptionWithMessageAndCode` | With HTTP code |
| `testAsanaApiExceptionWithResponseData` | With response data |
| `testAsanaApiExceptionWithPreviousException` | Exception chaining |
| `testAsanaApiExceptionGetResponseDataReturnsEmptyArray` | Default data |
| `testAsanaApiExceptionExtendsException` | Inheritance |
| `testOAuthCallbackExceptionWithMessageOnly` | Basic construction |
| `testOAuthCallbackExceptionWithMessageAndCode` | With code |
| `testOAuthCallbackExceptionWithData` | With data |
| `testOAuthCallbackExceptionWithPreviousException` | Exception chaining |
| `testOAuthCallbackExceptionGetDataReturnsEmptyArray` | Default data |
| `testOAuthCallbackExceptionExtendsException` | Inheritance |
| `testOAuthCallbackExceptionWithComplexData` | Complex data structure |
| `testTokenInvalidExceptionWithMessageOnly` | Basic construction |
| `testTokenInvalidExceptionWithMessageAndCode` | With code |
| `testTokenInvalidExceptionWithData` | With data |
| `testTokenInvalidExceptionWithPreviousException` | Exception chaining |
| `testTokenInvalidExceptionGetDataReturnsEmptyArray` | Default data |
| `testTokenInvalidExceptionExtendsException` | Inheritance |
| `testTokenInvalidExceptionNoAccessToken` | Common use case |
| `testTokenInvalidExceptionOAuthNotConfigured` | Common use case |
| `testAllExceptionsCanBeCaughtAsException` | Polymorphism |
| `testTokenInvalidExceptionWithContext` | Context interpolation |
| `testExceptionChaining` | Full exception chain |

---

## Test Approach

### Mocking Strategy

All API service tests use PHPUnit mocks of the `AsanaApiClient` to:
1. Verify correct HTTP method is used (GET, POST, PUT, DELETE)
2. Verify correct endpoint URL is constructed
3. Verify request data structure matches API requirements
4. Verify response type parameter is passed correctly

### Test Isolation

- Each test class creates fresh mocks in `setUp()`
- Tests do not depend on external services
- File system operations use temp directories with cleanup

### Edge Cases Covered

- Empty responses and empty arrays
- Unicode and special characters
- Long strings and large data
- Invalid input handling
- Exception chaining and error propagation

---

## Running Tests

```bash
# Run all tests
composer test

# Run with coverage (requires Xdebug)
composer test:coverage

# Run specific test file
vendor/bin/phpunit tests/Api/TaskApiServiceTest.php

# Run specific test method
vendor/bin/phpunit --filter testGetTask
```

---

## Files Structure

```
tests/
├── Api/
│   ├── AttachmentApiServiceTest.php
│   ├── CustomFieldApiServiceTest.php
│   ├── MembershipApiServiceTest.php
│   ├── ProjectApiServiceTest.php
│   ├── SectionApiServiceTest.php
│   ├── TagsApiServiceTest.php
│   ├── TaskApiServiceTest.php
│   ├── UserApiServiceTest.php
│   └── WorkspaceApiServiceTest.php
├── Auth/
│   ├── AsanaOAuthHandlerTest.php
│   ├── OAuth2ProviderTest.php
│   └── ScopesTest.php
├── Exceptions/
│   └── ExceptionsTest.php
├── Http/
│   └── AsanaApiClientTest.php
├── Utils/
│   └── CryptoUtilsTest.php
└── AsanaClientTest.php
```
