# Larry Mayers Portfolio SaaS - Project Handoff

Last updated: 2026-05-31

## Purpose

This project is a custom PHP portfolio SaaS application. It is both a public professional portfolio and a private CMS/admin tool for managing structured career content.

The guiding idea is simple: build a credible public portfolio, then support it with SaaS-style admin workflows for content management.

## Current Product State

The application now feels like a version 1.0 product:

- Public portfolio pages exist for profile/home, about, contact, resume, and projects.
- Resume entries are database-driven and render publicly as card-based content.
- Project entries are database-driven and render publicly as card-based content with associated technologies.
- Admin authentication exists.
- A protected dashboard exists and now has a SaaS-style control center UI.
- Admin can manage resume entries.
- Admin can manage project entries.
- Messages can be viewed from the admin area.
- The app uses SQLite for persistence.
- The frontend uses progressive JavaScript enhancements, not a full frontend framework.

## Active Architecture

The active rebuild lives in these paths:

```txt
public/
  index.php
  assets/
    css/
      main.css
      style.css
    js/
      app.js
      script.js
    img/

src/
  Core/
    Auth.php
    Database.php
    Request.php
    Response.php
    Router.php
    View.php

  Controllers/
    AdminController.php
    DashboardController.php
    MessageController.php
    ProjectController.php
    ResumeController.php
    HomeController.php
    AboutController.php
    ContactController.php

views/
  pages/
    dashboard.php
    messages.php
    projects.php
    project-create.php
    project-edit.php
    project-manage.php
    resume.php
    resume-create.php
    resume-edit.php
    resume-manage.php
    home.php
    about.php
    contact.php
    login.php

  partials/
    header.php
    header-nav.php
    footer.php

database/
  app.sqlite
  app_test.sqlite
  schema.sql
  seed.sql
```

Older code still exists under `public/deprecated--archive/`, `core/`, and `app/`. Treat those as legacy references unless deliberately migrating something.

## Deployment Model

The project has been deployed to Hostinger using a safer structure:

```txt
public_html/
  index.php
  .htaccess
  assets/

larrymayers-app/
  src/
  views/
  database/
  config/
```

Only browser-accessible files should live under `public_html`.

The front controller should use a root path similar to:

```php
$rootDir = __DIR__ . '/../larrymayers-app/';
```

Apache rewrite rules are required so URLs like `/resume`, `/projects/manage`, and `/resume/edit?id=4` route through `index.php`.

Recommended `.htaccess` in `public_html`:

```apache
Options -Indexes

RewriteEngine On
RewriteBase /

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

RewriteRule ^ index.php [QSA,L]
```

The `QSA` flag matters because edit URLs depend on query strings such as `?id=4`.

## Routing

Routes are currently defined in:

```txt
src/Core/Router.php
```

Important active routes:

```txt
GET  /                  -> home
GET  /profile           -> home
GET  /about             -> about
GET  /contact           -> contact
POST /contact           -> contactPost

GET  /resume            -> public resume catalog
GET  /resume/manage     -> protected resume manager
GET  /resume/create     -> protected resume create form
POST /resume/create     -> protected resume create action
GET  /resume/edit?id=ID -> protected resume edit form
POST /resume/update     -> protected resume update action
POST /resume/delete     -> protected resume delete action

GET  /projects          -> public projects catalog
GET  /projects/manage   -> protected project manager
GET  /projects/create   -> protected project create form
POST /projects/create   -> protected project create action
GET  /projects/edit?id=ID -> protected project edit form
POST /projects/update   -> protected project update action

GET  /login             -> login form
POST /login             -> login action
POST /logout            -> logout action
GET  /dashboard         -> protected admin dashboard
GET  /messages          -> messages inbox
POST /messages/delete   -> delete message
```

## Database

SQLite is the current persistence layer.

Main database:

```txt
database/app.sqlite
```

Schema source of truth:

```txt
database/schema.sql
```

Important tables:

```txt
users
messages
resume
duties
projects
project_technologies
project_duties
expertise
```

Important relationships:

- `resume` has many `duties`
- `projects` has many `project_technologies`
- `projects` may later have many `project_duties`
- child tables use `ON DELETE CASCADE`

Current `Database.php` also creates/migrates some columns at runtime using helper methods such as `createProjectsTable()` and `createProjectTechnologiesTable()`. This helped during development, but long-term schema changes should be kept in `database/schema.sql`.

## Major Development Successes

### Rebuild Direction

The project was moved away from a mixed legacy/static structure toward a clear PHP app shape:

- public web root
- core PHP helpers
- controllers
- views
- SQLite database
- browser assets

This made the app easier to reason about and safer to deploy.

### View Path Fix

`View.php` originally used fragile relative paths such as:

```php
require "./../../views/pages/{$view_name}.php";
```

This broke depending on the working directory. The fix was to base view paths on `__DIR__`:

```php
$viewsPath = __DIR__ . "/../../views";
```

### Responsive Navigation

The header navigation was redesigned with:

- brand area
- responsive nav links
- accessible hamburger button
- mobile collapse behavior
- JavaScript toggle

The click handler was also adjusted so hash-only links do not accidentally trigger SPA-style navigation.

### Query String Navigation Fix

The async navigation layer originally used:

```js
navigate(url.pathname);
```

This dropped query strings. Clicking `/resume/edit?id=4` became `/resume/edit`, causing the PHP controller to report a missing ID.

The fix preserved `url.search`:

```js
navigate(`${url.pathname}${url.search}`);
```

The same fix was applied to browser back/forward navigation.

### Resume CRUD

Resume CRUD became the first stable protected CMS workflow:

- public `/resume` remains open
- admin routes require login
- create/update/delete are form-driven
- duties are saved as child rows
- public resume cards render from database content

The public resume view was later corrected to use the same card layout as projects.

### Projects CMS

Projects CMS now supports:

- public project catalog
- protected management screen
- create form
- edit form
- update action
- project technologies as child rows

A major bug was found and fixed in `saveProjectTechnologies()`.

Broken version:

```sql
INSERT INTO project_technologies (project_id, technology)
VALUES (:project_id, :technology, :order_index)
```

Fixed version:

```sql
INSERT INTO project_technologies (project_id, technology, order_index)
VALUES (:project_id, :technology, :order_index)
```

### Dashboard UI

The admin dashboard was upgraded from a plain list of links into a SaaS-style control center with:

- signed-in session panel
- metric cards for messages, projects, and resume entries
- content management action cards
- operations/status panel
- responsive dashboard layout

Dashboard metrics are loaded from SQLite in `DashboardController.php`.

## Development Failures and Lessons

### Duplicate Active Files

There were multiple CSS and PHP file locations:

```txt
assets/css/main.css
public/assets/css/main.css
public/deprecated--archive/...
core/...
app/...
```

The active browser-loaded CSS is:

```txt
public/assets/css/main.css
```

When deployed to Hostinger, this becomes:

```txt
public_html/assets/css/main.css
```

Lesson: edit the file served by the active document root.

### CSS Override Confusion

`style.css` was loaded after `main.css`, meaning it could override theme styles.

Recommended long-term fix:

- migrate useful rules from `style.css` into `main.css`
- stop loading `style.css`, or load it before `main.css`

### Legacy Database Class

At one point `public/index.php` loaded:

```php
core/databases/Database.php
```

instead of:

```php
src/Core/Database.php
```

That caused mismatched database paths and missing methods. The active rebuild should use `src/Core/Database.php`.

### Duplicate Function/Class Methods

There was a duplicate `createQualificationsTable()` method in `Database.php`, which caused a fatal redeclaration error.

Lesson: run `php -l` after controller/core edits.

### Function Name Collisions

Global helper functions such as `isAjaxRequest()`, `jsonResponse()`, and `redirectTo()` were initially duplicated across controllers.

The better direction is to keep shared response helpers in:

```txt
src/Core/Response.php
```

and avoid redefining them in controllers.

### GET/POST Handler Confusion

The Projects CMS initially mixed up:

- create form display
- create form submission

Correct pattern:

```txt
GET  /projects/create -> projectCreateForm()
POST /projects/create -> projectCreate()
```

Same pattern should apply to future CMS modules.

### Destructive Table Clearing

`projectDb()` temporarily called `clearProjectsTable()`, which deleted project records during normal requests.

That was removed. Database initialization should create/migrate tables, never clear production content during page load.

### Broken HTML Layout

The resume public view had a grid-closing `</div>` inside the loop. That made only the first card behave correctly.

Lesson: when templating cards, keep wrapper elements outside loops and card elements inside loops.

## Current UI System

The UI is intentionally dark, technical, and admin-tool friendly.

Primary stylesheet:

```txt
public/assets/css/main.css
```

Important utility/component classes:

```txt
container
section
grid
grid-2
grid-3
card
card-body
btn
btn-primary
btn-secondary
tag-list
tag
muted
surface
lead
eyebrow
```

Dashboard-specific classes:

```txt
dashboard-shell
dashboard-hero
dashboard-session
dashboard-alert
dashboard-stats
stat-card
dashboard-grid
dashboard-panel
action-list
action-card
action-icon
ops-list
dashboard-actions
```

## Current Verification Commands

Use these before committing or deploying:

```powershell
php -l public\index.php
php -l src\Core\Database.php
php -l src\Core\Router.php
php -l src\Core\View.php
php -l src\Controllers\DashboardController.php
php -l src\Controllers\ProjectController.php
php -l src\Controllers\ResumeController.php
php -l views\pages\dashboard.php
php -l views\pages\projects.php
php -l views\pages\resume.php
```

Run locally with:

```powershell
php -S localhost:8000 -t public
```

## Current Known Gaps

These are not necessarily bugs, but they should be addressed later.

### Security

- Add CSRF protection to all POST forms.
- Review session cookie security settings for Hostinger.
- Add authorization checks for all admin/CMS routes.
- Avoid exposing SQLite files under web-accessible paths.

### Architecture

- Controllers currently contain SQL and business workflow logic.
- Long-term direction should introduce models/services:

```txt
src/Models/Project.php
src/Models/ResumeEntry.php
src/Services/ProjectService.php
src/Services/ResumeService.php
```

This is not urgent until workflows stabilize.

### Database

- Keep `database/schema.sql` synchronized with runtime table helpers.
- Add a proper seed workflow for admin user creation.
- Decide whether `app.sqlite` should be committed or generated per environment.

### Admin UX

- Add delete for projects.
- Improve project edit/update feedback.
- Add archive/read state for messages.
- Add toast/flash component.
- Add clearer admin navigation.

### Content

- Replace placeholder text on public pages.
- Add real project descriptions and links.
- Add skills/expertise and certifications modules.

## Suggested Next Milestones

### Milestone 1: Stabilize CMS Foundations

- Confirm all current routes work on Hostinger.
- Add CSRF tokens.
- Add project delete.
- Add consistent flash messages.
- Confirm admin dashboard metrics work in production.

### Milestone 2: Admin Navigation and Layout

- Create a dedicated admin layout or admin sidebar.
- Keep public navigation separate from admin workflows.
- Add consistent page headers to all admin pages.

### Milestone 3: Skills and Certifications CMS

- Create tables:

```txt
skills
skill_categories
certifications
```

- Build CRUD following the Projects/Resume pattern.
- Render public skills/certifications pages or sections.

### Milestone 4: Refactor to Services

Once CRUD workflows are stable, move repeated SQL and transaction logic out of controllers.

Suggested first extraction:

```txt
src/Services/ProjectService.php
src/Services/ResumeService.php
```

### Milestone 5: Production Hardening

- Error handling
- Logging
- Backup/restore for SQLite
- `.htaccess` hardening
- Cache-busting for assets
- Deployment checklist

## Important Mental Model

The public site is the product surface.

The admin/CMS area is the engine behind the product.

Build new features only when they improve one of these:

- public credibility
- admin maintainability
- data integrity
- deployment safety
- learning value

## Restart Checklist

When returning to the project:

1. Read this file first.
2. Read `PROJECT_CONTEXT.md` for intent, but note it contains encoding artifacts and some aspirational rules.
3. Read `DEVELOPMENT_STRATEGY.md` for broader planning.
4. Run `git status --short`.
5. Run the PHP syntax checks listed above.
6. Start local server with `php -S localhost:8000 -t public`.
7. Verify:
   - `/`
   - `/projects`
   - `/resume`
   - `/login`
   - `/dashboard`
   - `/projects/manage`
   - `/resume/manage`
8. Continue from the next milestone rather than reopening older architecture debates.
