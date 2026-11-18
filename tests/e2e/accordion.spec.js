// @ts-check
const { test, expect } = require("@playwright/test");

/**
 * Regression tests for the two bugs PHPUnit's cURL-based smoke tests
 * structurally cannot catch -- see tests/Smoke/AccordionMarkupTest.php's
 * docblock for the full explanation. Both tests below are written against
 * the exact real incidents, not generic coverage:
 *
 *   1. A CSS cascade conflict (public/assets/css/style.css's old
 *      `#app { background-color: #f9f9f9; }`) made the dark theme's
 *      light text invisible. cURL sees the right class names in the
 *      markup and has no way to know that; only a real rendering engine
 *      can compute the actual background/text colors.
 *   2. This app replaces #app's entire innerHTML via fetch() on every
 *      internal link click (see public/assets/js/app.js's navigate()),
 *      so a click handler wired up once at initial page load goes stale
 *      the moment a visitor navigates through the site normally. cURL
 *      cannot execute JavaScript or simulate a click at all.
 */

test.describe("Resume accordion", () => {
  test("clicking a resume entry after a direct page load reveals its body", async ({ page }) => {
    await page.goto("/resume");

    const entry = page.locator(".resume-entry", { hasText: "Playwright Fixture Role" });
    const body = entry.locator(".resume-entry-body");

    await expect(body).not.toBeVisible();
    await entry.locator("summary").click();
    await expect(body).toBeVisible();
    await expect(body).toContainText("Verified the accordion opens on click");
  });

  test("clicking a resume entry after navigating through the site's own nav link still works", async ({ page }) => {
    // This is the exact scenario that was actually broken: a visitor
    // browsing normally (clicking the nav link, not loading /resume
    // directly) triggers the SPA's innerHTML swap, which replaces the
    // <details> elements with ones that were never wired up by a
    // page-load-time listener. The fix (event delegation in script.js)
    // is specifically what this test is proving still holds.
    await page.goto("/");
    await page.getByRole("link", { name: "Resume" }).click();
    await page.waitForURL("**/resume");

    const entry = page.locator(".resume-entry", { hasText: "Playwright Fixture Role" });
    const body = entry.locator(".resume-entry-body");

    await expect(body).not.toBeVisible();
    await entry.locator("summary").click();
    await expect(body).toBeVisible();
  });

  test("resume entry text is actually readable against its background", async ({ page }) => {
    // Direct regression test for the #app background-color conflict --
    // asserts the real computed colors, not just that a class name with
    // the right intent is present in the markup.
    await page.goto("/resume");

    const role = page.locator(".resume-role").first();
    const colors = await role.evaluate((el) => {
      const style = window.getComputedStyle(el);
      const appBackground = window.getComputedStyle(document.getElementById("app")).backgroundColor;
      return { text: style.color, appBackground };
    });

    // The old bug: #app painted rgb(249, 249, 249) under light near-white
    // text. Pin both halves of the regression down explicitly.
    expect(colors.appBackground).not.toBe("rgb(249, 249, 249)");
    expect(colors.text).toBe("rgb(244, 246, 251)"); // var(--text), #f4f6fb
  });
});

test.describe("Project accordion", () => {
  test("clicking a project entry after a direct page load reveals its body", async ({ page }) => {
    await page.goto("/projects");

    const entry = page.locator(".project-entry", { hasText: "Playwright Fixture Project" });
    const body = entry.locator(".project-entry-body");

    await expect(body).not.toBeVisible();
    await entry.locator("summary").click();
    await expect(body).toBeVisible();
    await expect(body).toContainText("Playwright");
    await expect(body.getByRole("link", { name: "View Project" })).toBeVisible();
  });

  test("clicking a project entry after navigating through the site's own nav link still works", async ({ page }) => {
    await page.goto("/");
    await page.getByRole("link", { name: "Projects" }).click();
    await page.waitForURL("**/projects");

    const entry = page.locator(".project-entry", { hasText: "Playwright Fixture Project" });
    const body = entry.locator(".project-entry-body");

    await expect(body).not.toBeVisible();
    await entry.locator("summary").click();
    await expect(body).toBeVisible();
  });
});
