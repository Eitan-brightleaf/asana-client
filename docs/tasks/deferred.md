# Deferred Improvements for Asana Client

This document tracks thoughtful improvements that have been evaluated but intentionally postponed. Each item includes a rationale and conditions under which it may be revisited.

---

### 🐳 Docker-Based Development Environment

**Status:** Deferred

> We considered adding a Docker-based setup for consistent development environments, but since the library currently has only one primary developer and a stable local setup, it's unnecessary. This may be revisited if we begin onboarding contributors or if the dev environment becomes more complex.

Originally suggested in: `build-deployment-improvements.md`

---

### 🧠 Response Caching Support

**Status:** Deferred

> Caching `GET` responses could reduce redundant API calls, but caching logic is best handled by users of the library based on their infrastructure (Redis, Symfony Cache, etc.). We may revisit this if there's user demand or common caching patterns emerge.

Originally suggested in: `performance-improvements.md`

---

### 🧵 Memory Optimization for Bulk Operations

**Status:** Deferred

> The library already plans on supporting pagination iterators, which address most memory concerns. Stream-based helpers or callback-style APIs may be added later if users experience high memory usage with large datasets.

Originally suggested in: `performance-improvements.md`

---

### 📈 CI Code Coverage Reporting

**Status:** Deferred

> Code coverage is currently tracked locally via PHPStorm. Since there are no collaborators at this time, coverage reports in CI are unnecessary. We may add Codecov or HTML reports in the future when team size or complexity increases.

Originally suggested in: `testing-improvements.md`

---

### 🧬 Mutation Testing with Infection

**Status:** Deferred

> Mutation testing ensures test robustness by checking whether your tests catch small, deliberate code changes. It's powerful but slow. We'll revisit this once we have strong baseline test coverage and aim to increase confidence in regressions.

Originally suggested in: `testing-improvements.md`

---

Feel free to propose reactivating any of these in a future PR if you believe the timing is right.
