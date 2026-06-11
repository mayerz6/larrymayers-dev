# Testing and CI/CD Strategy

## Purpose

This document defines the testing strategy for the portfolio SaaS application as it grows from a custom PHP portfolio into a CMS-backed product with authenticated admin workflows, public content delivery, and deployment to Hostinger.

The goal is not to test everything equally. The goal is to protect stable behavior as new features are added, especially resume CRUD, project CMS, blog CMS, authentication, routing, database persistence, and public page rendering.

## Current Application Profile

The application is a custom PHP web application with:

- Public pages for profile, about, resume, projects, blog, and contact.
- Authenticated admin pages for dashboard and CMS management.
- Form-driven CRUD workflows for resume, projects, and blog content.
- SQLite persistence through `src/Core/Database.php`.
- URL routing through `src/Core/Router.php` and `public/index.php`.
- Progressive asynchronous frontend behavior through `public/assets/js/app.js`.
- Hostinger deployment where only browser-accessible assets belong in `public_html`.

This means the highest-risk areas are request routing, authentication protection, database writes, async form behavior, and regressions in public content rendering.

## Enterprise Testing Mindset

An enterprise-quality test strategy should provide confidence at multiple levels:

- Fast tests catch syntax, structure, and business-logic mistakes before code review.
- Integration tests prove PHP controllers, routing, database behavior, and response formats work together.
- End-to-end tests prove real user workflows work in a browser.
- Security checks catch common web application risks before deployment.
- Deployment smoke tests verify the production environment is serving the expected version.

The test suite should be automated, repeatable, isolated from production data, and required before merging or deploying.

## Recommended Test Layers

### 1. Static and Syntax Checks

These are the first gate because they are fast and cheap.

Required checks:

- `php -l` for all PHP files.
- JavaScript syntax check with `node --check public/assets/js/app.js`.
- Optional PHP style check with PHP_CodeSniffer.
- Optional static analysis with PHPStan or Psalm.

What this protects:

- Broken PHP syntax.
- Missing braces or malformed views.
- JavaScript parse failures.
- Basic type and function-call mistakes.

Recommended future commands:

```bash
find src views public -name "*.php" -print0 | xargs -0 -n1 php -l
node --check public/assets/js/app.js
vendor/bin/phpstan analyse src
```

### 2. Unit Tests

Unit tests should cover isolated logic that does not require a browser or full HTTP request.

Best candidates in this application:

- Path parsing and route matching.
- Input normalization helpers such as duties, technologies, and future blog tags.
- Response helper behavior.
- Auth helper logic where session state can be controlled.
- Database schema creation methods using a temporary test database.

Example unit test targets:

- `normalizeDuties()` returns trimmed non-empty lines.
- `normalizeTechnologies()` returns trimmed non-empty lines.
- `isAjaxRequest()` correctly detects `X-Requested-With`.
- Database table creation methods create expected tables.

Recommended tool:

- PHPUnit.

Suggested structure:

```text
tests/
  Unit/
    NormalizeDutiesTest.php
    NormalizeTechnologiesTest.php
    ResponseTest.php
    DatabaseSchemaTest.php
```

Enterprise expectation:

- Unit tests should be fast enough to run on every commit.
- Unit tests should not touch the real `database/app.sqlite`.
- Tests should create and destroy isolated test data.

### 3. Integration Tests

Integration tests prove that controllers, routing, database writes, redirects, and JSON responses work together.

Best candidates:

- `POST /login` returns success JSON for valid credentials.
- Protected CMS routes redirect or deny access when not logged in.
- `POST /resume/create` inserts a resume entry and duties.
- `POST /projects/create` inserts a project and technologies.
- `POST /blog/create` inserts a blog post.
- `POST /blog/update` updates an existing blog post.
- Public pages render entries from the database.

Important behaviors to assert:

- HTTP status code.
- Redirect path for normal form submissions.
- JSON payload for AJAX submissions.
- Database row count changed as expected.
- Invalid input returns a controlled error.
- Unauthenticated users cannot write CMS content.

Suggested structure:

```text
tests/
  Integration/
    AuthRoutesTest.php
    ResumeCrudTest.php
    ProjectCrudTest.php
    BlogCrudTest.php
    PublicPagesTest.php
```

Enterprise expectation:

- Integration tests should run against `database/app_test.sqlite`, never production data.
- Each test should reset the database to a known state.
- Tests should verify both success and failure cases.

### 4. End-to-End Browser Tests

End-to-end tests prove that a real user can use the system through the browser.

Recommended tool:

- Playwright.

Critical workflows:

- Public visitor opens home, resume, projects, blog, and contact pages.
- Contact form submits asynchronously and displays success or failure feedback.
- Admin logs in successfully.
- Admin creates a resume entry and sees it on the public resume page.
- Admin creates a project with technologies and sees it on the public projects page.
- Admin creates a blog post asynchronously and sees it on the public blog page.
- Admin edits a blog post and sees the updated content.
- Admin logs out and cannot access protected admin routes.

Suggested structure:

```text
tests/
  e2e/
    public-pages.spec.js
    auth.spec.js
    resume-cms.spec.js
    project-cms.spec.js
    blog-cms.spec.js
```

Enterprise expectation:

- E2E tests should run in CI before deployment.
- Screenshots and traces should be saved on failure.
- Tests should use seeded test data, not manual production data.
- Tests should cover desktop and mobile viewport basics for navigation.

### 5. Accessibility Tests

Accessibility should be part of regression testing, especially because this is a portfolio and professional showcase.

Recommended checks:

- Keyboard navigation works.
- Focus states are visible.
- Forms have labels.
- Buttons and links are semantically correct.
- Color contrast passes WCAG AA where possible.
- Navigation menu works on mobile.

Recommended tooling:

- Playwright with Axe.
- Lighthouse CI for public pages.

Enterprise expectation:

- Accessibility failures should be treated as defects, not polish.
- Critical public pages should be checked in CI.

### 6. Security Tests

Security tests are required because the application has authenticated write access.

Critical checks:

- Protected routes require login.
- Direct POST requests to CMS routes fail without login.
- Passwords are hashed and never logged.
- User input is escaped in views with `htmlspecialchars()`.
- SQL writes use prepared statements.
- Invalid IDs are rejected cleanly.
- Session logout invalidates admin access.

Recommended future additions:

- CSRF tokens for all POST forms.
- Rate limiting for login and contact form submissions.
- Security headers in production.
- Dependency vulnerability checks once Composer/NPM dependencies are introduced.

Enterprise expectation:

- Every authenticated write route should have a test proving unauthenticated access is blocked.
- Every public rendering path should escape stored user content.
- No test should require real production credentials.

### 7. Visual Regression Tests

Visual tests are useful once the UI stabilizes.

Best candidates:

- Home page.
- Dashboard.
- Resume cards.
- Project cards.
- Blog management pages.
- Mobile navigation.

Recommended tooling:

- Playwright screenshots.
- Percy, Chromatic, or a lightweight screenshot diff workflow.

Enterprise expectation:

- Use visual regression for stable layouts, not rapidly changing drafts.
- Review visual diffs manually before approval.

## Minimum Viable Test Suite

The first practical milestone should be small but valuable.

Phase 1 test coverage:

- PHP syntax check for all PHP files.
- JS syntax check for `public/assets/js/app.js`.
- PHPUnit tests for normalization helpers.
- Integration tests for protected route access.
- Integration tests for blog create/update JSON behavior.
- Playwright test for login and blog post creation.

This gives immediate protection around the newest CMS feature without slowing development too much.

## Recommended Test Database Strategy

Do not run tests against `database/app.sqlite`.

Recommended setup:

```text
database/
  app.sqlite
  app_test.sqlite
  schema.sql
```

Expected behavior:

- Local development uses `app.sqlite`.
- Automated tests use `app_test.sqlite`.
- CI creates `app_test.sqlite` from `schema.sql`.
- Tests seed only the data they need.
- Tests delete or recreate `app_test.sqlite` before each full run.

Future improvement:

- Make the database path configurable with an environment variable:

```text
APP_ENV=testing
DB_PATH=database/app_test.sqlite
```

## CI/CD Flow

The CI/CD pipeline should run in stages. Each stage blocks the next one.

### Pull Request Pipeline

Runs on every pull request or branch push.

Recommended stages:

1. Checkout code.
2. Install PHP dependencies.
3. Install Node dependencies if Playwright or JS tooling is introduced.
4. Create test database.
5. Run PHP syntax checks.
6. Run JavaScript syntax checks.
7. Run unit tests.
8. Run integration tests.
9. Run browser tests.
10. Upload test artifacts on failure.

Expected result:

- Code cannot merge unless all required checks pass.

### Main Branch Pipeline

Runs after merging to the main production branch.

Recommended stages:

1. Repeat all pull request checks.
2. Build or package deployment artifact.
3. Deploy to staging environment.
4. Run staging smoke tests.
5. Deploy to production.
6. Run production smoke tests.

Expected result:

- Production deployment only happens after tests pass against a staging-like environment.

### Production Smoke Tests

These are lightweight tests after deployment.

Recommended checks:

- `/` returns 200.
- `/resume` returns 200.
- `/projects` returns 200.
- `/blog` returns 200.
- `/login` returns 200.
- Static CSS and JS assets load.
- Admin routes are not publicly accessible without login.

Smoke tests should not create production CMS content unless a controlled test account and cleanup process exist.

## Example GitHub Actions Pipeline

This is a future-oriented example. It assumes Composer, PHPUnit, and Node tooling have been added.

```yaml
name: CI

on:
  pull_request:
  push:
    branches:
      - main

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: "8.2"
          extensions: pdo_sqlite

      - name: Install PHP dependencies
        run: composer install --no-interaction --prefer-dist

      - name: Validate PHP syntax
        run: find src views public -name "*.php" -print0 | xargs -0 -n1 php -l

      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: "20"

      - name: Validate JavaScript syntax
        run: node --check public/assets/js/app.js

      - name: Prepare test database
        run: |
          rm -f database/app_test.sqlite
          sqlite3 database/app_test.sqlite < database/schema.sql

      - name: Run unit and integration tests
        run: vendor/bin/phpunit

      - name: Install Playwright
        run: npx playwright install --with-deps

      - name: Run browser tests
        run: npx playwright test
```

## Deployment Considerations for Hostinger

For Hostinger deployment, CI/CD should preserve the security boundary:

- Browser-accessible files go into `public_html`.
- Application source, database files, config, and views should live outside public web root where possible.
- Deployment should not overwrite production database files unless intentionally migrating.
- Environment-specific config should not be committed.

Recommended deployment safety checks:

- Confirm `public_html/index.php` points to the correct application root.
- Confirm `.sqlite` files are not browser-accessible.
- Confirm debug output is disabled in production.
- Confirm PHP errors are logged privately, not displayed publicly.

## Release Checklist

Before deploying a meaningful change:

- PHP syntax checks pass.
- JavaScript syntax checks pass.
- Unit tests pass.
- Integration tests pass.
- E2E tests pass for affected workflows.
- Manual review confirms public pages render correctly.
- Protected routes require login.
- Database backup exists before schema-changing deployments.
- Deployment smoke tests pass.

## Suggested Implementation Roadmap

### Phase 1: Foundation

- Add Composer if not already present.
- Install PHPUnit.
- Add `tests/Unit` and `tests/Integration`.
- Make database path configurable for testing.
- Add syntax-check scripts.
- Add tests for helper functions and protected routes.

### Phase 2: CMS Regression Coverage

- Add integration tests for resume CRUD.
- Add integration tests for project CRUD.
- Add integration tests for blog create/update.
- Add tests for failed validation.
- Add tests proving unauthenticated POST requests are blocked.

### Phase 3: Browser and Pipeline Automation

- Install Playwright.
- Add login, dashboard, and CMS workflow tests.
- Add responsive navigation checks.
- Add GitHub Actions or equivalent CI pipeline.
- Add staging smoke tests before production deployment.

## Definition of Done for Future Features

A feature should not be considered complete until:

- It has validation for bad input.
- It handles unauthenticated access correctly.
- It has at least one automated test for the successful path.
- It has at least one automated test for the failure path.
- It does not break existing public pages.
- It is documented if it changes routes, database schema, or deployment behavior.

## Practical Priority

The next best testing investment is not full browser automation. The next best move is to add PHPUnit and a test database, then cover authenticated CMS POST routes. That gives the most protection for the current application because the highest-value logic lives in controller workflows and database writes.

