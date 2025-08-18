# Documentation Improvements

This document outlines documentation enhancements needed for the Asana Client PHP library. Each item includes detailed explanations, code examples, and validation against API specifications.

## 1. Create a contributing guide

### Problem Statement
The project lacks a contributing guide that explains how to set up the development environment, coding standards, and the pull request process, making it difficult for external contributors to participate.

### Code Examples

#### Current Implementation:
```markdown
# CONTRIBUTING.md exists at the repository root
```

#### Expected Implementation:
```markdown
# Contributing to Asana Client PHP Library

Thank you for considering contributing to the Asana Client PHP library! This document provides guidelines and instructions for contributing to the project.

## Development Environment Setup

1. **Fork and Clone the Repository**

   git clone https://github.com/YOUR_USERNAME/asana-client.git
   cd asana-client

2. **Install Dependencies**

   composer install

3. **Set Up Environment Variables**

   Create a `.env` file in the project root with the following variables:

   ASANA_CLIENT_ID=your_test_client_id
   ASANA_CLIENT_SECRET=your_test_client_secret
   ASANA_REDIRECT_URI=http://localhost:8000/callback
   ASANA_TEST_PAT=your_test_personal_access_token
   SALT=your_test_encryption_salt

4. **Run Tests**

   composer test

## Coding Standards

This project follows the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard. To ensure your code complies with these standards, run:

composer lint

To automatically fix most coding standard issues:

composer lint:fix

## Testing

All new features and bug fixes should include tests. This project uses PHPUnit for testing.

### Running Tests

# Run all tests
composer test

# Run with coverage report
composer test:coverage

# Run mutation tests
composer test:mutation

### Writing Tests

- Unit tests should be placed in the `tests/Unit` directory
- Integration tests should be placed in the `tests/Integration` directory
- Test files should follow the same namespace structure as the source code
- Test classes should end with `Test` (e.g., `TaskApiServiceTest`)
- Test methods should begin with `test` and have descriptive names

Example test:

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\TaskApiService;
use BrightleafDigital\Http\ApiClient;
use PHPUnit\Framework\TestCase;

class TaskApiServiceTest extends TestCase
{
    private $mockClient;
    private $taskService;
    
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(ApiClient::class);
        $this->taskService = new TaskApiService($this->mockClient);
    }
    
    public function testGetTaskReturnsTaskData()
    {
        $taskId = '12345';
        $expectedResponse = ['data' => ['gid' => $taskId, 'name' => 'Test Task']];
        
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', "tasks/{$taskId}", ['query' => []])
            ->willReturn($expectedResponse);
        
        $result = $this->taskService->getTask($taskId);
        $this->assertEquals($expectedResponse, $result);
    }
}

## Pull Request Process

1. **Create a Feature Branch**

   git checkout -b feature/your-feature-name

2. **Make Your Changes**

   Implement your changes, following the coding standards and adding tests.

3. **Run Tests and Linting**

   composer test
   composer lint

4. **Commit Your Changes**

   Use clear and descriptive commit messages that explain what changes were made and why.

5. **Push to Your Fork**

   git push origin feature/your-feature-name

6. **Submit a Pull Request**

   - Fill out the pull request template with all required information
   - Link to any related issues
   - Describe what changes were made and why
   - Include any breaking changes or deprecations

7. **Code Review**

   - A maintainer will review your pull request
   - Address any feedback or requested changes
   - Once approved, your pull request will be merged

## Documentation

When adding new features or changing existing functionality, please update the relevant documentation:

- Update PHPDoc comments for all classes and methods
- Update the README.md if necessary
- Add or update examples in the `examples` directory

## Versioning

This project follows [Semantic Versioning](https://semver.org/). When proposing changes, consider whether they are:

- **Patch** (1.0.0 -> 1.0.1): Bug fixes that don't change the API
- **Minor** (1.0.0 -> 1.1.0): New features that don't break backward compatibility
- **Major** (1.0.0 -> 2.0.0): Changes that break backward compatibility

## Questions?

If you have any questions about contributing, please open an issue or contact the maintainers.

Thank you for your contributions!
```

### File References
- `CONTRIBUTING.md`: Exists at repository root

### API Spec Validation
The contributing guide should encourage contributors to validate their changes against the Asana API specification to ensure compliance.

### Critical Evaluation
- **Actual Impact**: Medium - Lack of contributing guidelines discourages external contributions (addressed)
- **Priority Level**: Medium - Should be addressed to encourage community involvement
- **Implementation Status**: Implemented - CONTRIBUTING.md added at repository root
- **Spec Compliance**: N/A - This is a development process concern
- **Difficulty/Complexity**: Low - Primarily involves creating documentation and establishing development guidelines with minimal technical complexity

### Recommended Action
Maintain and iterate on the contributing guide as the project evolves (tooling, scripts, scopes, APIs). Ensure the guide continues to encourage validation against the Asana API specification.

## 2. Create changelog and versioning documentation

### Problem Statement
The project lacks versioning documentation, making it difficult for users to understand versioning policy and upgrade guidance. A changelog now exists and should be maintained.

### Code Examples

#### Current Implementation:
```markdown
# CHANGELOG.md exists at the repository root
```

#### Expected Implementation:
```markdown
# Changelog

All notable changes to the Asana Client PHP library will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Support for cursor-based pagination in all list endpoints
- New `EventApiService` for working with Asana events

### Changed
- Improved error handling with more detailed exception messages
- Enhanced rate limiting with automatic retries

### Fixed
- Fixed issue with OAuth token refresh when tokens expire

## [1.2.0] - 2023-09-15

### Added
- Support for custom fields in tasks and projects
- New methods for managing task dependencies
- Added `SectionApiService` for working with sections

### Changed
- Improved performance of batch requests
- Updated API client to use Guzzle 7.x

### Fixed
- Fixed issue with file uploads larger than 10MB
- Corrected parameter handling in project creation

## [1.1.0] - 2023-06-10

### Added
- Support for webhooks
- New methods for managing task subtasks
- Added support for task templates

### Changed
- Improved OAuth flow with better state management
- Enhanced error messages for API errors

### Fixed
- Fixed issue with date formatting in task due dates
- Corrected pagination handling in list methods

## [1.0.0] - 2023-03-01

### Added
- Initial release of the Asana Client PHP library
- Support for tasks, projects, users, and workspaces
- OAuth 2.0 and Personal Access Token authentication
- Comprehensive test suite

## Upgrading

### Upgrading from 1.1.x to 1.2.x

This is a minor release with backward-compatible changes. However, note the following:

- The `CustomFieldApiService` has new methods that may conflict with any custom extensions
- Batch request format has been optimized for better performance

### Upgrading from 1.0.x to 1.1.x

This is a minor release with backward-compatible changes. However, note the following:

- OAuth flow now requires state parameter validation
- Pagination handling has changed slightly for better efficiency

## Versioning Policy

This project follows [Semantic Versioning](https://semver.org/):

- **MAJOR** version increments for incompatible API changes
- **MINOR** version increments for new functionality in a backward-compatible manner
- **PATCH** version increments for backward-compatible bug fixes

## Release Schedule

- **Patch releases**: As needed for bug fixes
- **Minor releases**: Approximately every 3 months for new features
- **Major releases**: Announced well in advance with migration guides
```

### Changelog Generation Tools

For automated changelog generation, consider using PHP tools like:
- [php-conventional-changelog](https://github.com/marcocesarato/php-conventional-changelog): Generates changelogs from conventional commit messages
- [keep-a-changelog](https://github.com/loophp/keep-a-changelog): PHP library to manipulate CHANGELOG files that follow the Keep a Changelog format
- [git-changelog-generator](https://github.com/milo/git-changelog-generator): Simple PHP script to generate changelog from Git history

### File References
- `CHANGELOG.md`: Exists at repository root
- `docs/versioning.md`: To be created for versioning policy

### API Spec Validation
The changelog should note any changes in the library's implementation that relate to changes or updates in the Asana API specification.

### Critical Evaluation
- **Actual Impact**: Medium - Lack of versioning documentation makes version management difficult
- **Priority Level**: Medium - Should be addressed to improve version management
- **Implementation Status**: Partially implemented - CHANGELOG.md exists; versioning documentation not yet created
- **Spec Compliance**: N/A - This is a development process concern
- **Difficulty/Complexity**: Low - Primarily involves creating documentation and establishing versioning policies with minimal technical implementation

### Recommended Action
Keep CHANGELOG.md up to date following the Keep a Changelog format. Create `docs/versioning.md` to document the project’s versioning policy (Semantic Versioning) and provide upgrade guidance.