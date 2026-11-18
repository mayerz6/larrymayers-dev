// @ts-check
const { test, expect } = require("@playwright/test");

/**
 * Regression test for the hamburger-to-X icon: the top and bottom bars
 * used to be spaced by CSS Grid's implicit row auto-placement, and the
 * expanded-state transform (translateY(7px)/(-7px)) assumed a specific
 * 7px gap between them that the grid wasn't actually guaranteed to
 * produce. When the real spacing didn't match, both bars rotated toward
 * the center but met off-axis instead of crossing through the same
 * point -- rendering as a lopsided arrow/chevron, not a clean X.
 *
 * The fix pins both bars to the exact same explicit center point
 * (top: 50%) in the expanded state, so this test checks that guarantee
 * directly via geometry rather than a screenshot: after the button is
 * clicked, the first and third bars must share the same vertical center,
 * which is only true if they're actually crossing rather than just
 * overlapping near one edge.
 */
test.use({ viewport: { width: 375, height: 812 } }); // narrow enough to trigger the mobile nav-toggle

test("the hamburger icon's two outer bars cross at the same center point when expanded", async ({ page }) => {
  await page.goto("/");

  const toggle = page.locator(".nav-toggle");
  const bars = toggle.locator("span");

  await expect(toggle).toBeVisible();
  await expect(toggle).toHaveAttribute("aria-expanded", "false");

  await toggle.click();
  await expect(toggle).toHaveAttribute("aria-expanded", "true");

  // The nav links should actually reveal too -- the programmatic behavior
  // the user confirmed already works, kept here so this test still fails
  // loudly if that regresses, not just the icon's geometry.
  await expect(page.locator("#primary-navigation")).toBeVisible();

  const [topBarBox, bottomBarBox] = await Promise.all([
    bars.nth(0).boundingBox(),
    bars.nth(2).boundingBox(),
  ]);

  if (!topBarBox || !bottomBarBox) {
    throw new Error("Could not measure the hamburger icon's bars.");
  }

  const topBarCenterY = topBarBox.y + topBarBox.height / 2;
  const bottomBarCenterY = bottomBarBox.y + bottomBarBox.height / 2;

  // A real X requires both bars to cross through the same point. Before
  // the fix, these two values differed by several pixels (however much
  // the grid's actual row spacing diverged from the 7px the old CSS
  // assumed) -- that gap is exactly what made it look like ">" instead
  // of "X". A couple of pixels of tolerance covers sub-pixel rounding,
  // nothing more.
  expect(Math.abs(topBarCenterY - bottomBarCenterY)).toBeLessThanOrEqual(1);
});
