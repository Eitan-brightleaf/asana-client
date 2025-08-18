# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog, and this project adheres to Semantic Versioning.

## v.0.1.2 - 2025-08-18

### Changed
- Re-ordered and expanded OAuth scopes for consistency and coverage (commits: 5847f9b, e890ebe, 42e26f8, 515548b).
- Expanded API method doc blocks with detailed response structures and additional examples (b66ba0f).
- Replaced `$fullResponse` boolean with a constant to allow returning only `data` when desired (aff5b32).

### Added
- Added docs directory with roadmap and updated README and .gitignore accordingly (684b76e).
- Added new folders scaffolding (59e28dd).
- Improved CryptoUtils utility class (2f056dc).
- Example clarifications in code comments (b8301f6).
- Added this changelog (20e2720).

### Documentation
- Misc documentation updates (1da2a38).

## v0.1.1 - 2025-06-24

### Added
- API endpoint and HTTP method information included in API method doc blocks (b18ceef).

## v0.1.0 - 2025-05-08

### Added
- New static constructor for OAuth flows (e1364a2).
- Crypto utilities class to encrypt and decrypt tokens (e5540fa).
- PHPDoc/docblocks for encryption methods (5aea165).

### Changed
- Updated manual refresh method to always refresh (3f7ce2a).
- Updated utility methods to use new encryption methods (01c216a).
- Normalize line endings to LF (c954878).

### Fixed
- Fixed method call and comment cleanups (03daeba, 08edd22).

### Documentation
- Updated README (ef74633).

## v0.0.4 - 2025-05-01

### Changed
- Refresh token method now includes the refresh token in the returned token payload (a609608).

## v0.0.3 - 2025-04-30

### Added
- Callback hooks to token refresh flow (f6a170f).
- Example: viewing project custom fields (4a7a7a5).
- Getter for retrieving the current access token (8138f81).

### Changed
- Ensure access token return is JSON-serializable; refactor return handling (6da36b8, 90c2ea1).

### Fixed
- File upload method docs and stream handling issues (1796469).
- Example fixes and minor adjustments (8e2e219, 6da83c6).

### Documentation
- Update README with pre-1.0 roadmap and minor text fixes (db77f40).

### Chore
- Ignore VSCode files (d79c6af).

## v0.0.2 - 2025-04-22

### Added
- Support for OAuth scopes (ee4f031).
- CustomFieldApiService and WorkspaceApiService (9f42e7a).
- Initial tests for AsanaClient (52a4760).

### Changed
- ApiClient returns the whole response body (not just `data`); examples updated (a3743e1, 24231f0).
- Composer constraints updated to avoid exact version pinning; PHP version requirements clarified (99736c1, eefb0a2, e380e2e).

### Documentation
- README usage examples and authentication details (304fac7).
- README updates (e23f711).

### Chore
- Added .aiignore to .gitignore (3a1aacc).

## v0.0.1 - 2025-04-03

Initial tagged release.

### Added
- Asana PHP client basic file structure and OAuth2 scaffolding.
- Multiple API service classes (Tasks, Projects, Workspaces, Users, Tags, etc.).
- Examples for common workflows.
- Error handling improvements and custom exceptions.
