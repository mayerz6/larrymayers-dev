# Portfolio SaaS — Backlog (Jira-style)

Project key: `PORT`. Generated 2026-11-05 from `PROJECT_HANDOFF.md`'s "Current Known Gaps" plus findings from a full codebase review. Priorities: **P0** blocks safe production use, **P1** should land soon, **P2** nice-to-have / polish.

---

## Epic: PORT-SEC — Security Hardening

### PORT-1 — Add CSRF protection to all state-changing forms
**Type:** Security | **Priority:** P0

**Description:**
No POST route (`/contact`, `/resume/create`, `/resume/update`, `/resume/delete`, `/projects/create`, `/projects/update`, `/blog/create`, `/blog/update`, `/messages/delete`, `/login`) currently validates a CSRF token. Any page that can trick a logged-in admin's browser into submitting a cross-site POST can act on their behalf.

**Acceptance Criteria:**
- A CSRF token is generated per session and embedded in every form in `larrymayers-app/views/pages/*`.
- Every POST handler in `src/Controllers/*` rejects requests with a missing/invalid token (400/403).
- Contact form (`/contact`) is in scope even though it's public-facing, to block automated spam submission replay.

---

### PORT-2 — Fix session key collision between portfolio auth and Cartana
**Type:** Security | **Priority:** P0

**Description:**
`larrymayers-app/src/Core/Auth.php::requireAuth()` gates all admin routes by checking `$_SESSION['user_id']`. `public/dev-projects/cartana/v1/auth.php` sets `$_SESSION['user_id']` and `$_SESSION['is_admin']` on its own, unrelated login. Both apps share one domain and, by default, one PHP session cookie. A user authenticated in one app currently satisfies the auth check of the other.

**Acceptance Criteria:**
- Cartana and the portfolio no longer share a session namespace (separate session name via `session_name()`, separate cookie path, or a namespaced session key such as `$_SESSION['cartana']['user_id']`).
- Manually verified: logging into Cartana does not grant access to `/dashboard` on the portfolio, and vice versa.
- This must be resolved before Cartana (or any future `dev-projects/*` app) is deployed live on `larrymayers.site`.

---

### PORT-3 — Decide repo strategy for `public/dev-projects/*` and fix `.gitignore`
**Type:** Security / Repo Hygiene | **Priority:** P0

**Description:**
`public/dev-projects/cartana/v1/` has its own nested `.git` pointing at a separate GitHub repo, and the portfolio's `.gitignore` does not exclude it (the existing `projects/` rule does not match `dev-projects/`). As-is, a `git add -A` in the portfolio repo risks committing Cartana's nested `.git` as a broken gitlink, plus its real SQLite data file (`products.db`).

**Acceptance Criteria:**
- One of: (a) `public/dev-projects/` added to `.gitignore` and treated as a deploy-time-only folder not tracked by the portfolio repo, or (b) each project under `dev-projects/` is added as a proper `git submodule`.
- `products.db` and any future `.env` files under `dev-projects/*` are confirmed never committed to either repo's history.
- `git status --short` in the portfolio repo shows no untracked files under `dev-projects/` after the fix.

---

### PORT-4 — Fix Cartana `BASE_PATH` / `.htaccess` mismatch
**Type:** Bug | **Priority:** P1

**Description:**
`public/dev-projects/cartana/v1/config.php` defines `BASE_PATH` as `/projects/cartana/v1`, but `public/dev-projects/cartana/v1/.htaccess` uses `RewriteBase /dev-projects/cartana/v1/`. These two values must agree for internal links/redirects to resolve correctly.

**Acceptance Criteria:**
- `BASE_PATH` and `RewriteBase` point to the same path.
- Internal navigation inside the Cartana app (login redirect, pricing page, checkout) works end to end at `/dev-projects/cartana/v1/`.

---

### PORT-5 — Review session cookie security flags for Hostinger
**Type:** Security | **Priority:** P1

**Description:**
No explicit `session_set_cookie_params()` call was found. Cookie `Secure`, `HttpOnly`, and `SameSite` flags are currently whatever PHP's defaults are on the Hostinger box.

**Acceptance Criteria:**
- Session cookie is set with `Secure`, `HttpOnly`, and `SameSite=Lax` (or stricter) in production.
- Confirmed via browser dev tools against the live Hostinger deployment.

---

### PORT-6 — Add authorization checks consistently across all admin/CMS routes
**Type:** Security | **Priority:** P1

**Description:**
`requireAuth()` exists in `Auth.php`, but each controller calls it individually. Audit every handler in `ResumeController.php`, `ProjectController.php`, `BlogController.php`, `MessageController.php`, `DashboardController.php` to confirm there is no path (GET or POST) that mutates or reveals admin-only data without calling `requireAuth()` first.

**Acceptance Criteria:**
- A checklist mapping every route in `Router.php` to "requires auth: yes/no" is produced and matches intent.
- Any route found missing the check is fixed.

---

### PORT-7 — Confirm SQLite files are not web-accessible in production
**Type:** Security | **Priority:** P1

**Description:**
`larrymayers-app/database/app.sqlite` is outside `public/`, which is correct locally. This has not yet been verified against the live Hostinger `public_html`/`larrymayers-app` layout.

**Acceptance Criteria:**
- A direct request to the production equivalent of `database/app.sqlite` returns 404, not the raw file.
- Same check performed for `public/dev-projects/cartana/v1/products.db` and `.env` (once one exists).

---

## Epic: PORT-ARCH — Architecture Cleanup

### PORT-8 — Extract service layer out of controllers
**Type:** Tech Debt | **Priority:** P2

**Description:**
`ResumeController.php`, `ProjectController.php`, and `BlogController.php` each contain SQL, validation, and HTTP response logic inline. `src/Models/*` are empty stub files.

**Acceptance Criteria:**
- `src/Services/ResumeService.php` and `src/Services/ProjectService.php` created, repeated SQL/transaction logic moved there.
- Controllers call services and only handle request/response concerns.
- Not started until CRUD workflows are confirmed stable per `PROJECT_CONTEXT.md`'s existing rule — do not do this speculatively.

---

### PORT-9 — Wire up the missing single blog-post route
**Type:** Bug | **Priority:** P1

**Description:**
`BlogController.php` has `fetchBlogPost(PDO $db, int $id)` and `views/pages/blog-post.php` exists, but `Router.php` has no `GET /blog/post` (or `/blog/{id}`-style) entry. Public visitors can't open an individual post from the blog listing.

**Acceptance Criteria:**
- A route (e.g. `GET /blog/post?id=ID`) is added, wired to a new `blogPost()` handler using the existing `fetchBlogPost()` helper.
- Blog listing links to the new route and it renders the full post.

---

### PORT-10 — Remove or finish the `qualifications` feature stub
**Type:** Tech Debt | **Priority:** P2

**Description:**
`Database.php::createQualificationsTable()` exists but there's no `qualifications` table in `schema.sql`, no route, and no controller. Looks abandoned.

**Acceptance Criteria:**
- Either removed entirely, or finished with a schema entry, route, and admin CRUD screen matching the Resume/Projects pattern.

---

### PORT-11 — Remove dead `site_visits` / `site_stats` tables or implement analytics
**Type:** Tech Debt | **Priority:** P2

**Description:**
Both tables exist in `schema.sql` with no reader or writer anywhere in `src/Controllers/*`.

**Acceptance Criteria:**
- Either removed from `schema.sql`, or a minimal page-view tracker is added that writes to them and the dashboard reads from them.

---

### PORT-12 — Rewrite `Readme.MD` and `PROJECT_CONTEXT.md`
**Type:** Documentation | **Priority:** P2

**Description:**
Both files still document the deleted `core/` MVC structure (routes.php/logic.php style) as if it were current.

**Acceptance Criteria:**
- Both files updated to describe the `public/` + `larrymayers-app/` structure, or `Readme.MD` is replaced with a short pointer to `PROJECT_HANDOFF.md`.

---

## Epic: PORT-DB — Database

### PORT-13 — Add a seed workflow for the admin user
**Type:** Tech Debt | **Priority:** P1

**Description:**
`Database::insertDefaultUser()` hardcodes a plaintext password (`M@y3rZ!2#`) directly in source, which then gets hashed. This credential is visible to anyone with repo access.

**Acceptance Criteria:**
- Default admin password is read from an environment variable or a local-only `seed.sql`/`.env`, not committed to git in cleartext.
- Existing committed password is rotated.

---

### PORT-14 — Decide whether `app.sqlite` should be committed
**Type:** Tech Debt | **Priority:** P2

**Description:**
`larrymayers-app/database/app.sqlite` currently appears to be a real data file tracked in git. This mixes schema (should be versioned) with content (shouldn't be, especially once real admin credentials or contact-form PII land in it).

**Acceptance Criteria:**
- Decision recorded: either keep `app.sqlite` out of git and rely on `schema.sql` + `seed.sql` to bootstrap it, or explicitly accept it's committed and ensure it never contains real visitor PII.

---

## Epic: PORT-ADMIN — Admin UX

### PORT-15 — Add project delete
**Type:** Feature | **Priority:** P1

**Description:**
Projects CMS has create/edit/update but no delete action, unlike Resume which has `resumeDelete`.

**Acceptance Criteria:**
- `POST /projects/delete` route + handler added, following the same pattern as `resumeDelete`.
- Cascades to `project_technologies` and `project_duties` (schema already has `ON DELETE CASCADE`, confirm it fires).

---

### PORT-16 — Add flash/toast messaging component
**Type:** Feature | **Priority:** P2

**Description:**
`DashboardController.php` already reads/writes `$_SESSION['flash']` but there's no reusable UI component for it across all admin pages.

**Acceptance Criteria:**
- A shared partial renders `$_SESSION['flash']` consistently on every admin page, then clears it.

---

### PORT-17 — Add archive/read state for messages
**Type:** Feature | **Priority:** P2

**Acceptance Criteria:**
- `messages` table gets a `read_at` or `status` column.
- Inbox view distinguishes unread from read without requiring delete as the only resolution.

---

## Epic: PORT-TEST — Testing & CI/CD

(Source: `TESTING_CICD_STRATEGY.md`. Converted to concrete tickets.)

### PORT-18 — Add PHP/JS syntax check scripts
**Type:** Chore | **Priority:** P1

**Acceptance Criteria:**
- A script runs `php -l` over every file in `larrymayers-app/src`, `larrymayers-app/views`, and `public`.
- `node --check public/assets/js/app.js` runs in the same script.
- Both are runnable locally with one command and are the first stage of CI once CI exists.

---

### PORT-19 — Install PHPUnit and add unit tests for normalization helpers
**Type:** Testing | **Priority:** P1

**Acceptance Criteria:**
- `tests/Unit/NormalizeDutiesTest.php` and `tests/Unit/NormalizeTechnologiesTest.php` exist and pass.
- Tests run against `database/app_test.sqlite`, never `app.sqlite`.

---

### PORT-20 — Add integration tests proving unauthenticated POSTs are blocked
**Type:** Testing | **Priority:** P1

**Description:**
This is the single highest-value test per `TESTING_CICD_STRATEGY.md` — directly protects against the kind of auth gap PORT-6 is meant to catch.

**Acceptance Criteria:**
- One test per protected POST route (`resume/create`, `resume/update`, `resume/delete`, `projects/create`, `projects/update`, `blog/create`, `blog/update`, `messages/delete`) asserting a logged-out request is rejected or redirected, not executed.

---

### PORT-21 — Stand up GitHub Actions CI pipeline
**Type:** Chore | **Priority:** P2

**Acceptance Criteria:**
- PR pipeline runs PORT-18 and PORT-19/20 on every push.
- Pipeline blocks merge on failure.

---

## Epic: PORT-CONTENT — Content

### PORT-22 — Replace placeholder text on public pages
**Type:** Content | **Priority:** P2

### PORT-23 — Populate projects catalog from `Career_Projects.md`
**Type:** Content | **Priority:** P1

**Description:**
`Career_Projects.md` already contains 11 structured, resume-grounded project write-ups with suggested slugs (`/projects/aws-etl-operations`, etc.) that aren't yet in the `projects` table.

**Acceptance Criteria:**
- Each project in `Career_Projects.md` is inserted into the `projects` (+ `project_technologies`, `project_duties`) tables via the admin UI or a seed script.

---

## Priority Summary

| Priority | Tickets |
|---|---|
| P0 | PORT-1, PORT-2, PORT-3 |
| P1 | PORT-4, PORT-5, PORT-6, PORT-7, PORT-9, PORT-13, PORT-15, PORT-18, PORT-19, PORT-20, PORT-23 |
| P2 | PORT-8, PORT-10, PORT-11, PORT-12, PORT-14, PORT-16, PORT-17, PORT-21, PORT-22 |

Recommended starting point: **PORT-2 and PORT-3 before Cartana touches the live domain at all** — both are data-exposure/privilege-escalation risks, not polish. PORT-1 (CSRF) is the next highest-value fix for the existing portfolio CMS itself.
