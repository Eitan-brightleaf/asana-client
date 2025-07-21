# Build and Deployment Improvements

This document outlines build and deployment enhancements needed for the Asana Client PHP library. Each item includes detailed explanations, code examples, and validation against API specifications.

## 1. Implement Continuous Integration

### Problem Statement
The project lacks a Continuous Integration (CI) system, which makes it difficult to ensure code quality and prevent regressions when changes are made. Implementing CI would automate testing, code quality checks, and other validation steps.

### Code Examples

#### Current Implementation:
```
# No CI configuration exists
```

#### Expected Implementation:
```yaml
# In .github/workflows/ci.yml
name: CI

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php-version: ['7.4', '8.0', '8.1']

    steps:
    - uses: actions/checkout@v2

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: ${{ matrix.php-version }}
        extensions: mbstring, intl, curl, json, openssl
        coverage: xdebug

    - name: Validate composer.json and composer.lock
      run: composer validate --strict

    - name: Cache Composer packages
      id: composer-cache
      uses: actions/cache@v2
      with:
        path: vendor
        key: ${{ runner.os }}-php-${{ hashFiles('**/composer.lock') }}
        restore-keys: |
          ${{ runner.os }}-php-

    - name: Install dependencies
      run: composer install --prefer-dist --no-progress

    - name: Check coding standards
      run: composer lint

    - name: Run static analysis
      run: composer analyze

    - name: Run tests
      run: composer test

    - name: Upload coverage to Codecov
      uses: codecov/codecov-action@v1
      with:
        file: ./coverage.xml
```

```json
{
    "name": "brightleaf-digital/asana-client",
    "description": "PHP client library for the Asana API",
    "type": "library",
    "license": "MIT",
    "require": {
        "php": "^7.4|^8.0",
        "guzzlehttp/guzzle": "^7.0",
        "league/oauth2-client": "^2.6",
        "psr/log": "^1.1|^2.0|^3.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.5",
        "squizlabs/php_codesniffer": "^3.6",
        "phpstan/phpstan": "^1.0",
        "infection/infection": "^0.25.0"
    },
    "autoload": {
        "psr-4": {
            "BrightleafDigital\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "BrightleafDigital\\Tests\\": "tests/"
        }
    },
    "scripts": {
        "test": "phpunit",
        "test:coverage": "phpunit --coverage-html coverage --coverage-clover coverage.xml",
        "test:mutation": "infection --threads=4 --min-msi=80",
        "lint": "phpcs src tests",
        "lint:fix": "phpcbf src tests",
        "analyze": "phpstan analyse src tests --level=7"
    },
    "config": {
        "sort-packages": true
    }
}
```

**File: composer.json with CI configuration**


### File References
- `.github/workflows/ci.yml`: New CI workflow configuration
- `composer.json`: Updated with scripts for testing, linting, and analysis

### API Spec Validation
CI implementation doesn't directly relate to API specification compliance, but it helps ensure that the code correctly implements the API specification by running automated tests and checks.

### Critical Evaluation
- **Actual Impact**: High - Without CI, code quality issues and regressions may go undetected
- **Priority Level**: High - Should be addressed early to improve development workflow
- **Implementation Status**: Not implemented - No CI system exists
- **Spec Compliance**: N/A - This is a development process concern
- **Difficulty/Complexity**: Medium - Requires understanding CI/CD concepts and GitHub Actions, but follows established patterns and workflows

### Recommended Action
Implement a CI workflow using GitHub Actions or a similar service. Configure it to run tests, code quality checks, and other validation steps on pull requests and commits to main branches.

## 2. Add Composer scripts for common tasks

### Problem Statement
The project lacks standardized Composer scripts for common development tasks, which makes it harder for contributors to run tests, linting, and other tasks consistently.

### Code Examples

#### Current Implementation:
In composer.json (minimal or non-existent scripts section)
```json
{
    "scripts": {
        "test": "phpunit"
    }
}
```

#### Expected Implementation:
```json
{
    "scripts": {
        "test": "phpunit",
        "test:coverage": "phpunit --coverage-html coverage --coverage-clover coverage.xml",
        "test:mutation": "infection --threads=4 --min-msi=80",
        "lint": "phpcs src tests",
        "lint:fix": "phpcbf src tests",
        "analyze": "phpstan analyse src tests --level=7",
        "docs": "phpDocumentor -d src -t docs/api",
        "check": [
            "@lint",
            "@analyze",
            "@test"
        ],
        "pre-commit": [
            "@lint",
            "@analyze"
        ]
    }
}
```

### File References
- `composer.json`: Needs updates to include scripts for common tasks

### API Spec Validation
Composer scripts don't directly relate to API specification compliance, but they help ensure consistent development practices that can improve compliance.

### Critical Evaluation
- **Actual Impact**: Medium - Without standardized scripts, development tasks may be performed inconsistently
- **Priority Level**: Medium - Should be addressed to improve development workflow
- **Implementation Status**: Minimal - Limited or no Composer scripts exist
- **Spec Compliance**: N/A - This is a development process concern
- **Difficulty/Complexity**: Low - Simple configuration changes to composer.json with straightforward script definitions

### Recommended Action
Add Composer scripts for all common development tasks, including testing, linting, static analysis, and documentation generation. Document these scripts in the contributing guide.

## 3. Implement semantic versioning

### Problem Statement
The project may not follow semantic versioning principles, which can make it difficult for users to understand the impact of updates and manage dependencies effectively.

### Code Examples

#### Current Implementation:
```
# No explicit versioning policy or documentation
```

#### Expected Implementation:
```markdown
# In VERSIONING.md
# Versioning Policy

This project follows [Semantic Versioning](https://semver.org/) (SemVer).

## Version Format

Versions are formatted as `MAJOR.MINOR.PATCH`:

- **MAJOR** version increments for incompatible API changes
- **MINOR** version increments for new functionality in a backward-compatible manner
- **PATCH** version increments for backward-compatible bug fixes

## Pre-release Versions

Pre-release versions may be denoted by appending a hyphen and a series of dot-separated identifiers (e.g., `1.0.0-alpha.1`).

## Version Constraints in composer.json

When requiring this package, it's recommended to use the caret operator (`^`) to allow updates that include new features but not breaking changes:

{
    "require": {
        "brightleaf-digital/asana-client": "^1.0.0"
    }
}

## Breaking Changes

Breaking changes will only be introduced in MAJOR version increments. When a breaking change is introduced:

1. It will be clearly documented in the CHANGELOG.md file
2. Migration guides will be provided when necessary
3. Deprecation notices will be added in a previous release when possible

## Release Schedule

- **PATCH releases**: As needed for bug fixes
- **MINOR releases**: Approximately every 3 months for new features
- **MAJOR releases**: Only when necessary for breaking changes, announced well in advance
```

```php
// In src/AsanaClient.php
/**
 * Asana API Client
 * 
 * @package BrightleafDigital
 * @version 1.0.0
 */
class AsanaClient
{
    /**
     * The client version
     * 
     * @var string
     */
    const VERSION = '1.0.0';

    // Rest of the class...
}
```

### File References
- `VERSIONING.md`: New file documenting versioning policy
- `src/AsanaClient.php`: Main client class that should include version information

### API Spec Validation
Semantic versioning doesn't directly relate to API specification compliance, but it helps users understand when changes might affect their integration with the Asana API.

### Critical Evaluation
- **Actual Impact**: Medium - Without semantic versioning, users may encounter unexpected breaking changes
- **Priority Level**: Medium - Should be addressed to improve user experience
- **Implementation Status**: Unknown - Current versioning practices are not documented
- **Spec Compliance**: N/A - This is a development process concern
- **Difficulty/Complexity**: Low - Primarily involves creating documentation and establishing versioning policies with minimal code changes

### Recommended Action
Implement and document a semantic versioning policy. Include version information in the code and ensure that all releases follow the policy.

## 4. Implement automated release process

### Problem Statement
The project lacks an automated release process, which can make releases inconsistent and error-prone. Implementing an automated release process would ensure that all necessary steps are performed consistently.

### Code Examples

#### Current Implementation:
```
# No automated release process
```

#### Expected Implementation:
```yaml
# In .github/workflows/release.yml
name: Release

on:
  push:
    tags:
      - 'v*.*.*'

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
        with:
          fetch-depth: 0

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'
          extensions: mbstring, intl, curl, json, openssl
          coverage: xdebug

      - name: Validate composer.json and composer.lock
        run: composer validate --strict

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress

      - name: Run tests
        run: composer test

      - name: Generate API documentation
        run: composer docs

      - name: Generate changelog
        id: changelog
        uses: metcalfc/changelog-generator@v1.0.0
        with:
          myToken: ${{ secrets.GITHUB_TOKEN }}

      - name: Create GitHub release
        id: create_release
        uses: actions/create-release@v1
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
        with:
          tag_name: ${{ github.ref }}
          release_name: Release ${{ github.ref }}
          body: |
            ${{ steps.changelog.outputs.changelog }}
          draft: false
          prerelease: false

      - name: Upload API documentation
        uses: actions/upload-release-asset@v1
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
        with:
          upload_url: ${{ steps.create_release.outputs.upload_url }}
          asset_path: ./docs/api.zip
          asset_name: api-docs.zip
          asset_content_type: application/zip
```

```php
// In bin/release.php
#!/usr/bin/env php
<?php

/**
 * Release script for the Asana Client PHP library.
 * 
 * This script automates the release process by:
 * 1. Validating the version number
 * 2. Updating version numbers in files
 * 3. Creating a git tag
 * 4. Pushing the tag to GitHub
 */

// Validate arguments
if ($argc !== 2) {
    echo "Usage: php bin/release.php <version>\n";
    echo "Example: php bin/release.php 1.0.0\n";
    exit(1);
}

$version = $argv[1];

// Validate version format
if (!preg_match('/^\d+\.\d+\.\d+(?:-[a-zA-Z0-9.]+)?$/', $version)) {
    echo "Error: Version must follow semantic versioning format (e.g., 1.0.0 or 1.0.0-alpha.1)\n";
    exit(1);
}

// Update version in AsanaClient.php
$clientFile = __DIR__ . '/../src/AsanaClient.php';
$clientContent = file_get_contents($clientFile);
$clientContent = preg_replace(
    '/const VERSION = \'[^\']+\';/',
    "const VERSION = '{$version}';",
    $clientContent
);
file_put_contents($clientFile, $clientContent);

// Update version in composer.json
$composerFile = __DIR__ . '/../composer.json';
$composerContent = file_get_contents($composerFile);
$composerJson = json_decode($composerContent, true);
$composerJson['version'] = $version;
file_put_contents($composerFile, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Create changelog entry
$changelogFile = __DIR__ . '/../CHANGELOG.md';
$changelogContent = file_get_contents($changelogFile);
$date = date('Y-m-d');
$changelogEntry = "## [{$version}] - {$date}\n\n### Added\n- \n\n### Changed\n- \n\n### Fixed\n- \n\n";
$changelogContent = preg_replace(
    '/# Changelog\n\n/',
    "# Changelog\n\n{$changelogEntry}",
    $changelogContent
);
file_put_contents($changelogFile, $changelogContent);

echo "Files updated with version {$version}\n";
echo "Now edit CHANGELOG.md to add release notes, then run:\n";
echo "git add src/AsanaClient.php composer.json CHANGELOG.md\n";
echo "git commit -m \"Release {$version}\"\n";
echo "git tag -a v{$version} -m \"Release {$version}\"\n";
echo "git push origin main v{$version}\n";
```

### File References
- `.github/workflows/release.yml`: GitHub Actions workflow for automated releases
- `bin/release.php`: Script to prepare a release
- `CHANGELOG.md`: Changelog file that should be updated for each release

### API Spec Validation
Release automation doesn't directly relate to API specification compliance, but it helps ensure that releases are properly tested and documented, which can improve compliance.

### Critical Evaluation
- **Actual Impact**: Medium - Without an automated release process, releases may be inconsistent
- **Priority Level**: Medium - Should be addressed to improve release quality
- **Implementation Status**: Not implemented - No automated release process exists
- **Spec Compliance**: N/A - This is a development process concern
- **Difficulty/Complexity**: Medium - Requires setting up GitHub Actions workflows and release scripts, but builds on existing CI/CD foundation

### Recommended Action
Implement an automated release process using GitHub Actions or a similar service. Create scripts to automate version updates, changelog generation, and other release tasks. Document the release process in the contributing guide.
