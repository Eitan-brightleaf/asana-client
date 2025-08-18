# Contributing to Asana Client PHP Library

Thank you for considering contributing to the Asana Client PHP library! This document provides guidelines and instructions for contributing to the project.

## Development Environment Setup

1. Fork and Clone the Repository

   ```bash
   git clone https://github.com/brightleaf-digital/asana-client.git
   cd asana-client
   ```

2. Install Dependencies

   ```bash
   composer install
   ```

3. Set Up Environment Variables

   Create a `.env` file in `examples/` with the following variables:

   ```bash
   ASANA_CLIENT_ID=your_test_client_id
   ASANA_CLIENT_SECRET=your_test_client_secret
   ASANA_REDIRECT_URI=http://localhost:8000/callback
   ASANA_TEST_PAT=your_test_personal_access_token
   PASSWORD=your_test_encryption_password
   ```

   Notes:
   - The SALT is used by the built-in encryption utilities for local development/testing only.
   - You can generate a Personal Access Token (PAT) in your Asana account settings for testing.

4. Run Tests

   ```bash
   composer test
   ```

   To generate a local coverage report:
   ```bash
   composer test:coverage
   ```

   To run a specific test or method, see examples below in the Testing section.

## Coding Standards

This project follows the PSR-12 coding standard. To check your code style locally, you can use PHP_CodeSniffer:

- Lint (check):
  ```bash
  ./vendor/bin/phpcs --standard=PSR12 src tests
  ```

- Auto-fix common issues:
  ```bash
  ./vendor/bin/phpcbf --standard=PSR12 src tests
  ```

Please ensure your code is clean and follows these standards before opening a pull request.

## Testing

All new features and bug fixes should include tests. This project uses PHPUnit for testing.

### Running Tests

- Run all tests
  ```bash
  ./vendor/bin/phpunit
  ```

- Run a specific test file
  ```bash
  ./vendor/bin/phpunit tests/path/to/TestFile.php
  ```

- Run a specific test method
  ```bash
  ./vendor/bin/phpunit --filter=methodName tests/path/to/TestFile.php
  ```

### Writing Tests

- Unit tests should be placed under the `tests` directory following the PSR-4 namespace structure mirroring `src/`
- Test files should follow the same namespace structure as the source code
- Test classes should end with `Test` (e.g., `TaskApiServiceTest`)
- Test methods should begin with `test` and have descriptive names

Example test:

```php
<?php

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
    
    public function testGetTaskReturnsTaskData(): void
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
```

## Pull Request Process

1. Create a Feature Branch

   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Make Your Changes

   Implement your changes, following the coding standards and adding tests.

3. Run Tests and Linting

  ```bash
  composer check
  # Optional: generate coverage locally
  composer test:coverage
  ```

4. Commit Your Changes

   Use clear and descriptive commit messages that explain what changes were made and why.

5. Push to Your Fork

   ```bash
   git push origin feature/your-feature-name
   ```

6. Submit a Pull Request

   - Fill out the pull request description with all required information
   - Link to any related issues
   - Describe what changes were made and why
   - Include any breaking changes or deprecations

7. Code Review

   - A maintainer will review your pull request
   - Address any feedback or requested changes
   - Once approved, your pull request will be merged

## Documentation

When adding new features or changing existing functionality, please update the relevant documentation:

- Update PHPDoc comments for all classes and methods
- Update the `readme.md` if necessary
- Add or update examples in the `examples` directory

## API Spec Validation

Whenever you add or modify API interactions, validate your changes against the Asana API specification to ensure
compliance. See the [OpenAPI specification here](https://github.com/Asana/openapi). Where possible:

- Confirm endpoint paths, required fields, and response structures
- Note any deviations or limitations in your pull request description

## Versioning

This project follows Semantic Versioning. When proposing changes, consider whether they are:

- Patch (1.0.0 -> 1.0.1): Bug fixes that don't change the API
- Minor (1.0.0 -> 1.1.0): New features that don't break backward compatibility
- Major (1.0.0 -> 2.0.0): Changes that break backward compatibility

## Questions?

If you have any questions about contributing, please open an issue or contact the maintainers.

Thank you for your contributions!


## Continuous Integration (CI)

This project uses GitHub Actions for CI. On every push and pull request to `main`:
- PHP matrix: 7.4, 8.0, 8.1, 8.2, 8.3
- Steps: `composer validate`, dependency install, `composer audit` (non-blocking), code style check (PSR-12), and PHPUnit tests with coverage
- Artifacts: coverage reports (clover.xml, junit.xml) are uploaded per PHP version
- Optimizations: docs-only changes may skip CI via paths-ignore, and in-progress runs on the same ref may be cancelled

Before opening a PR, please run locally:

```bash
composer check
# Optional: generate coverage locally
composer test:coverage
```
