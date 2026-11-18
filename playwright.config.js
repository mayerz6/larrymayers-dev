// @ts-check
const path = require("path");
const { defineConfig, devices } = require("@playwright/test");

/**
 * Real-browser end-to-end tests, the layer this project was specifically
 * missing: PHPUnit's cURL-based smoke tests (tests/Smoke/) can prove the
 * server rendered the right HTML, but cURL has no browser engine behind
 * it -- it cannot compute a CSS cascade and cannot execute JavaScript.
 * Two real bugs slipped past that layer in one session for exactly that
 * reason (an invisible-text CSS conflict, then a dead click handler after
 * the app's own SPA content-swap) while the markup itself stayed correct.
 * These tests exist to cover that gap -- see tests/e2e/accordion.spec.js.
 *
 * webServer below builds a disposable SQLite fixture (bootstrap-db.php,
 * same seeded admin credentials as the PHPUnit layer's SmokeServer.php)
 * and serves the real app against it with PHP's own built-in server --
 * the same command this project's README/handoff already tells a human
 * to run, not a special "test mode."
 */
module.exports = defineConfig({
  testDir: "./tests/e2e",
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: "list",

  use: {
    baseURL: "http://127.0.0.1:8900",
    trace: "retain-on-failure",
    screenshot: "only-on-failure",
  },

  projects: [
    {
      name: "chromium",
      use: { ...devices["Desktop Chrome"] },
    },
  ],

  webServer: {
    // No explicit router script argument here on purpose -- matching
    // PROJECT_HANDOFF.md's documented local-dev command exactly
    // (`php -S localhost:8000 -t public`). PHP's built-in server, given
    // only a docroot, serves real static files directly AND falls back
    // to public/index.php for any path that isn't a real file -- the
    // front-controller behavior this app actually needs. Adding
    // "public/index.php" as a second, explicit argument (my first
    // attempt here) disables that automatic static-file passthrough
    // entirely, which is exactly why main.css/script.js 404'd and every
    // page rendered unstyled, default-black-on-white, inside the browser
    // Playwright controls -- a real bug in this config, not the site.
    command:
      "php tests/e2e/bootstrap-db.php && php -S 127.0.0.1:8900 -t public",
    url: "http://127.0.0.1:8900/",
    reuseExistingServer: !process.env.CI,
    timeout: 20_000,
    env: {
      // Matches tests/e2e/bootstrap-db.php's $dbPath -- the app's real
      // database directory is larrymayers-app/database/, not a top-level
      // database/ folder (that mismatch was the exact cause of the
      // "unable to open database file" error on the first run).
      PORTFOLIO_DB_PATH: path.join(__dirname, "larrymayers-app", "database", "e2e_test.sqlite"),
    },
  },
});
