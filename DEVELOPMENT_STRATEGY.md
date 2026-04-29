# Larry Mayers Portfolio SaaS Development Strategy

## Product Direction

This project is being rebuilt as a lightweight portfolio SaaS application: a public professional portfolio with a private admin layer for managing content, messages, resume entries, and future publishing features.

The implementation should stay intentionally simple:

- Pure PHP backend with custom routing and server-rendered templates
- SQLite as the first database target
- Progressive JavaScript for async form submissions and partial UI updates
- No heavy framework dependency during the first production rebuild
- `public/` as the only web-accessible document root

The public site is the primary product. Admin tooling should support the public experience without driving the architecture too early.

## Target Folder Structure

```txt
/
  public/
    index.php
    assets/
      css/
        main.css
      js/
        app.js
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
      HomeController.php
      AboutController.php
      ContactController.php
      ResumeController.php
      ProjectController.php
      AdminController.php

    Models/
      Message.php
      ResumeEntry.php
      Project.php
      User.php

  views/
    layouts/
      app.php
      admin.php

    partials/
      header.php
      header-nav.php
      footer.php

    pages/
      home.php
      about.php
      contact.php
      resume.php
      projects.php

    admin/
      dashboard.php
      messages.php
      resume-manage.php
      projects-manage.php

  database/
    app.sqlite
    schema.sql
    seed.sql

  config/
    app.php
```

## Folder Responsibilities

### `public/`

The only directory that should be exposed by the web server. It contains the front controller, browser assets, images, CSS, and JavaScript.

### `src/Core/`

Framework-like application primitives. These files should not contain page-specific behavior.

- `Router.php`: route definitions and dispatch behavior
- `View.php`: view rendering and layout handling
- `Database.php`: PDO connection management
- `Request.php`: normalized access to request data
- `Response.php`: redirects, JSON responses, status codes
- `Auth.php`: session checks and login/logout helpers

### `src/Controllers/`

HTTP-facing application logic. Controllers should receive requests, call models or services, and return views, redirects, or JSON responses.

### `src/Models/`

Database-backed domain objects or query helpers. Keep SQL here instead of scattering SQL through templates.

### `views/`

Presentation only. Views should avoid database calls and heavy business logic.

### `database/`

Schema, seed data, and the development SQLite file. The schema should be the source of truth for tables.

### `config/`

Application settings such as app name, base URL, environment flags, database path, and mail configuration.

## Expected Final Features

### Public Portfolio

- Home/profile page with strong professional positioning
- About page
- Projects page with featured project cards
- Resume page generated from structured resume data
- Contact page with validated async submission
- Responsive navigation and footer
- SEO-friendly server-rendered HTML
- Clean typography and mobile-first layout

### Contact Pipeline

- Contact form validation
- SQLite persistence for messages
- JSON response for async frontend handling
- Basic spam controls
- Admin message inbox
- Optional email notification integration later

### Admin Area

- Admin login/logout
- Session-based protected routes
- Dashboard landing page
- Message inbox with delete/archive controls
- Resume entry create/update/delete
- Project create/update/delete
- Flash messages and JSON responses where appropriate

### Content Management

- Resume entries stored in SQLite
- Project entries stored in SQLite
- Optional blog/articles module after the core portfolio is stable
- Seed file for local setup

### Engineering Features

- Centralized routing
- Centralized view rendering
- Centralized database connection
- CSRF protection for state-changing forms
- Escaped output in templates
- Clear public document root
- Minimal async JavaScript enhancement

## Development Principles

- Build the public portfolio first.
- Add dynamic/admin features only after the static user experience is credible.
- Keep controllers thin and views passive.
- Prefer server-rendered HTML for pages.
- Use async JavaScript for forms and small UI updates, not full application state.
- Keep routes aligned with implemented controllers.
- Avoid carrying legacy `core/`, `app/`, or archived `public` logic into the rebuild unless deliberately migrated.

## Next Three Project Phases

## Phase 1: Backend Foundation and Public Pages

Goal: establish a clean PHP application foundation and complete the public-facing static portfolio routes.

### PHP Backend Logic

- Finalize `public/index.php` as the only front controller.
- Make all includes path-safe with `__DIR__`.
- Reduce active routes to implemented pages only.
- Implement or clean up:
  - `Router.php`
  - `View.php`
  - `Response.php`
  - `Request.php`
- Create public controllers:
  - `HomeController.php`
  - `AboutController.php`
  - `ContactController.php`
  - `ResumeController.php`
  - `ProjectController.php`
- Move repeated layout structure into `views/layouts/app.php`.
- Keep page views in `views/pages/`.

### Async Frontend JS Logic

- Keep JavaScript minimal in this phase.
- Preserve hamburger navigation.
- Avoid full SPA navigation until routes and views are stable.
- Add small helpers only if they improve progressive enhancement.

### Completion Criteria

- `php -S localhost:8000 -t public` serves the app.
- `/`, `/profile`, `/about`, `/contact`, `/resume`, and `/projects` load successfully.
- Header, footer, and layout render consistently.
- No active routes point to missing handlers.

## Phase 2: Contact Form and Database Layer

Goal: introduce real backend persistence safely, starting with the contact workflow.

### PHP Backend Logic

- Implement `Database.php` using PDO and SQLite.
- Define initial `database/schema.sql`.
- Create `messages` table.
- Implement `Message.php` model.
- Implement `ContactController::show()` and `ContactController::store()`.
- Validate contact form fields server-side.
- Escape output in all views.
- Return JSON for async contact submissions.
- Add CSRF token generation and validation for POST requests.

### Async Frontend JS Logic

- Add contact form submit handling.
- Show loading, success, and error states.
- Disable submit button while request is pending.
- Preserve non-JavaScript fallback if practical.

### Completion Criteria

- Contact form saves messages to SQLite.
- Invalid data returns useful validation errors.
- Successful async submission resets the form.
- Server-side validation works even if JavaScript is disabled.

## Phase 3: Admin SaaS Layer

Goal: add private management features that turn the portfolio into a small SaaS-style content platform.

### PHP Backend Logic

- Implement `users` table and seed admin user.
- Implement `Auth.php` session helpers.
- Add login/logout routes.
- Protect `/admin/*` routes.
- Create admin layout in `views/layouts/admin.php`.
- Build admin dashboard.
- Build message inbox:
  - list messages
  - view details
  - delete or archive messages
- Add resume management:
  - create entry
  - update entry
  - delete entry
  - order entries
- Add project management after resume management is stable.

### Async Frontend JS Logic

- Add async delete/archive controls for messages.
- Add inline resume/project form handling only where it improves workflow.
- Use optimistic UI carefully, with rollback on failed requests.
- Keep admin interactions scoped to admin pages.

### Completion Criteria

- Admin can log in and log out.
- Protected routes redirect unauthenticated users.
- Admin can manage contact messages.
- Admin can manage resume entries.
- Public resume page renders from database content.

## Recommended Immediate Next Task

Finish Phase 1 before adding more database or admin behavior.

The next concrete task should be:

1. Clean `Router.php` to only include implemented routes.
2. Convert `View.php` to use layouts instead of manual partial requires.
3. Build the five public pages with stable markup.
4. Run the site from `public/` and verify each route.

After that, move into the contact form/database slice.
