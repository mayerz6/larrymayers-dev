# UI Automation Testing Tutorial: Your First WebDriver Test

This is a beginner-level walkthrough of how `tests/PersonTest.php` drives a
real Chrome browser through PHPUnit. It doesn't introduce any new code — it
takes the *simplest* test already in the suite,
`testLoginWithIncorrectCredentialsIsRejectedAndKeepsTheUserOnTheLoginPage()`,
and explains it three times, at increasing levels of detail:

1. **Plain English** — what a human tester would do by hand.
2. **The code** — the exact PHP that performs each step.
3. **Pass/fail breakdown** — what the assertions check, and what a failure
   of each one would actually mean.

If you understand this one test, you understand the pattern behind every
other test in `tests/PersonTest.php` — they're all built from the same five
moves: **visit, find, act, wait, assert.**

---

## 0. The two pieces that make this possible

Before the walkthrough, it's worth naming the two files doing the work,
because the test itself never talks to Chrome directly:

| File | Role |
|---|---|
| `lib/webdriver.php` | The **client**. Defines `WebDriver` (a browser session — visit a URL, find something, wait, screenshot, quit) and `WebElement` (a single thing on the page — click it, type into it, read its text). |
| `tools/chromedriver.exe` | The **driver**. A small program from the Chromium project that listens on `http://127.0.0.1:9515`, receives plain HTTP requests from `WebDriver`, and translates them into real mouse clicks and keystrokes inside a real Chrome window. |

Your test never touches Chrome. It calls methods on a `WebDriver` object;
that object sends an HTTP request to chromedriver; chromedriver drives
Chrome; Chrome renders `http://localhost:9001` — your own dev server, the
same PHP app you're testing.

This is why chromedriver has to be started manually before running a
`#[Group('ui')]` test — it's a separate program, not something PHPUnit
launches for you:

```powershell
.\tools\chromedriver.exe --port=9515
```

If it's not running, `WebDriver::isRunning()` returns `false` and the test
calls `$this->markTestSkipped(...)` — you'll see `S` instead of `.` or `F` in
the PHPUnit output, and the run stays green. Skipped is not the same as
passed, and it's not the same as failed either — it means "this test didn't
get a chance to check anything."

---

## 1. Plain English: what a human tester would do

Imagine you were asked to manually test "what happens if someone types the
wrong password on the login page?" You'd do this:

1. Open a browser.
2. Go to `http://localhost:9001/login`.
3. Click into the **Email** field and type a made-up email address.
4. Click into the **Password** field and type an obviously wrong password.
5. Click the **Log In** button.
6. Look at the page that comes back. Is there an error message? Does it say
   something reasonable, like "incorrect email or password" — and *not*
   something that leaks whether the email exists at all?
7. Check the address bar — did it kick you back to `/login`, or did it (bug!)
   let you through to a dashboard anyway?
8. Check the password box — did it come back empty, or (bug!) is your typed
   password still sitting there in the field, visible to anyone glancing at
   the screen?
9. Take a screenshot, in case you need to show someone what you saw.

That's the entire test. Nothing about it is exotic — it's the same sequence
a QA tester clicking around by hand would follow. The only difference is that
every one of those nine steps below is now a line of PHP instead of a mouse
movement.

---

## 2. The code: overlaying PHP onto each step

Here is the real test, from `tests/PersonTest.php`, with the human steps
mapped onto it. (Some housekeeping — the `Person` actor object, comments —
is trimmed for focus; the full version is in the file.)

```php
#[Group('ui')]
public function testLoginWithIncorrectCredentialsIsRejectedAndKeepsTheUserOnTheLoginPage(): void
{
    // --- Arrange: decide who we're pretending to be ---
    $email    = sprintf('qa.reject.%s@example.invalid', bin2hex(random_bytes(4)));
    $password = 'definitely-not-the-password';

    $browser = $this->browser();   // Step 1: "open a browser"

    // --- Act ---
    $browser->visit(self::BASE_URL . self::LOGIN_PATH);   // Step 2: go to /login

    $browser->find('#email')->fill($email);       // Step 3: type into Email
    $browser->find('#password')->fill($password); // Step 4: type into Password
    $browser->find('button[type="submit"]')->click(); // Step 5: click Log In

    // --- Assert ---
    $alert = $browser->waitFor('.alert-danger', 15.0);   // Step 6: wait for the error banner

    $this->assertStringContainsStringIgnoringCase(
        'incorrect email or password',
        $alert->text()
    );

    $this->assertStringContainsString(               // Step 7: check the address bar
        self::LOGIN_PATH,
        $browser->currentUrl()
    );

    $this->assertSame(                                // Step 8: check the password field
        '',
        $browser->find('#password')->value()
    );

    $browser->saveScreenshot(__DIR__ . '/screenshots/login-rejected.png'); // Step 9
}
```

Walking through what each call actually does over the wire:

### Step 1 — `$this->browser()`

```php
private function browser(): WebDriver
{
    if (!WebDriver::isRunning()) {
        $this->markTestSkipped(/* ... */);
    }
    return $this->browser ??= WebDriver::chrome();
}
```

`WebDriver::chrome()` sends `POST /session` to chromedriver. Chromedriver
launches an actual Chrome process (headless — no visible window — by
default) and hands back a `sessionId`. From this point on, every other call
includes that ID in its URL, so chromedriver knows *which* browser window
you mean.

### Step 2 — `$browser->visit(...)`

```php
public function visit(string $url): static
{
    $this->send('POST', $this->path('/url'), ['url' => $url]);
    return $this;
}
```

This is `POST /session/<id>/url` with a JSON body of `{"url": "http://localhost:9001/login"}`.
Chromedriver tells Chrome to navigate, and waits for the page load to settle
before responding — that's what `pageLoadStrategy: 'normal'` (set once, back
in `WebDriver::chrome()`) means.

### Step 3–4 — `$browser->find('#email')->fill($email)`

Two things happen here, chained together:

```php
public function find(string $cssSelector): WebElement
{
    $found = $this->send('POST', $this->path('/element'), [
        'using' => 'css selector',
        'value' => $cssSelector,
    ]);
    return new WebElement($this, $this->elementIdFrom($found, $cssSelector));
}
```

`find('#email')` is `POST /session/<id>/element` — "search the current page
for the first thing matching this CSS selector." Chromedriver looks at the
live DOM and returns an opaque reference to whatever it found (not the text
or HTML — a *handle*, like a claim ticket). `find()` wraps that handle in a
`WebElement` object.

```php
public function fill(string $text): static
{
    return $this->clear()->type($text);
}
```

`fill()` is two more HTTP calls: `clear()` (`POST /session/<id>/element/<ref>/clear`
— empty the field) then `type()` (`POST /session/<id>/element/<ref>/value`
with `{"text": "qa.reject.xxxx@example.invalid"}` — send keystrokes). This
is exactly what `element.clear()` then typing character-by-character would
look like if you were watching it happen on screen.

### Step 5 — `->click()`

```php
public function click(): static
{
    $this->driver->elementRequest('POST', $this->id, '/click', []);
    return $this;
}
```

`POST /session/<id>/element/<ref>/click`. Chromedriver dispatches a real
mouse click at that element's on-screen position. This is what triggers the
form's actual `POST /login` request to your PHP app — the same request a
real browser would send.

### Step 6 — `$browser->waitFor('.alert-danger', 15.0)`

This is the step most beginners get wrong, so it's worth slowing down on.
After the click, the *page hasn't necessarily finished loading yet* — the
server has to process the login attempt and send back a new page. If you
called `find('.alert-danger')` immediately, you might be asking about a page
that hasn't arrived yet, and get an error instead of the element.

```php
public function waitFor(string $cssSelector, float $timeoutSeconds = 30.0): WebElement
{
    return $this->waitUntil(
        function () use ($cssSelector): ?WebElement {
            foreach ($this->findAll($cssSelector) as $element) {
                if ($element->isDisplayed()) {
                    return $element;
                }
            }
            return null;
        },
        $timeoutSeconds,
        sprintf('element "%s" to become visible', $cssSelector)
    );
}
```

`waitFor()` doesn't ask once — it asks *repeatedly*, every half-second (see
`waitUntil()`'s `usleep(500_000)`), until either the element shows up and is
actually visible, or 15 seconds pass with no luck. This is what "explicit
wait" means, and it's the standard fix for the single most common cause of
flaky browser tests: checking for something before the page has caught up.

### Steps 7–8 — the assertions

```php
$this->assertStringContainsString(self::LOGIN_PATH, $browser->currentUrl());
$this->assertSame('', $browser->find('#password')->value());
```

`currentUrl()` is `GET /session/<id>/url` — "what does the address bar say
right now?" `find('#password')->value()` re-finds the password input (it's a
fresh `find()` call, a new claim ticket — the old `WebElement` from step 4
pointed at a form that may have been replaced by the reloaded page) and asks
chromedriver to read its *live DOM property*, i.e. what's actually sitting in
that box right now, not what the HTML template originally set as a default.

### Step 9 — the screenshot

```php
public function saveScreenshot(string $path): string
{
    $base64 = (string) $this->send('GET', $this->path('/screenshot'));
    file_put_contents($path, base64_decode($base64, true) ?: '');
    return $path;
}
```

`GET /session/<id>/screenshot` returns a base64-encoded PNG of exactly what
the browser is currently showing. Decode it, write it to disk. This is why
`tests/screenshots/` fills up with `.png` files every time you run the `ui`
group — one snapshot per test, kept as evidence regardless of outcome.

---

## 3. The method and its assertions: what Pass and Fail actually mean

A single PHPUnit test method can contain several `assert*()` calls. **The
method passes only if every assertion inside it passes.** The moment one
assertion fails, PHPUnit stops running the rest of *that method* (later
methods still run) and reports the test red.

Here's what each assertion in this test is actually checking, and what a
failure would tell you:

| Assertion | Checks | If it PASSES | If it FAILS |
|---|---|---|---|
| `assertStringContainsStringIgnoringCase('incorrect email or password', $alert->text())` | The error banner's text contains that phrase, regardless of capitalization | The app correctly told the user their login was rejected | Either no error was shown, the wording changed, or (worse) the app leaked something like "no such user" — a message that confirms whether the email is registered |
| `assertStringContainsString('/login', $browser->currentUrl())` | The browser is still on a URL containing `/login` | A failed login did **not** grant access to anything | The app let a wrong password through to some other page — a serious authentication bug |
| `assertStringNotContainsStringIgnoringCase('no such user', $alert->text())` | The error message does **not** say this | The app doesn't distinguish "wrong email" from "wrong password" in its wording | The app is leaking which emails are registered — an account-enumeration vulnerability |
| `assertSame('', $browser->find('#password')->value())` | The password `<input>` is empty after the failed attempt | The typed password was not echoed back into the re-rendered form | Someone glancing at the screen (or reading a screenshot) could read the attempted password back out of the page |
| `assertFileExists($screenshot)` | The PNG file was actually written to disk | Evidence was captured for this run | `saveScreenshot()` failed silently, or the path was wrong — you'd have no picture to look at if something *else* had gone wrong |

**A "Passed" result** for this whole test means: I submitted a login with a
bogus, freshly-generated email and an obviously wrong password, and the app
responded exactly the way a secure login form should — rejected, vague about
which part was wrong, no session granted, no leaked password, and a green
`✔` in the PHPUnit output next to `Login with incorrect credentials is
rejected and keeps the user on the login page`.

**A "Failed" result** means at least one of those five checks came back
different from expected — and PHPUnit tells you *which one*, with the
expected value, the actual value, and a stack trace pointing at the exact
line. For example, if the app had a bug where a wrong password redirected to
`/dashboard` anyway, you'd see something like:

```
1) PersonTest::testLoginWithIncorrectCredentialsIsRejectedAndKeepsTheUserOnTheLoginPage
Failed asserting that '/dashboard' contains "/login".

C:\...\tests\PersonTest.php:225
```

That's the entire diagnostic value of the test: it doesn't just say "login
test failed," it says exactly *which* guarantee about the login page broke,
with the real URL the browser actually ended up on.

**An "Error" result** (different from a failure) means something in the
*mechanics* went wrong before an assertion even got a chance to run — for
example the `WebDriverException` you'd see if chromedriver crashed mid-test,
or if a CSS selector like `#email` stopped matching anything because the
markup changed. That's not the app's login logic being wrong; it's the test
itself needing repair (e.g. update the `SEL_EMAIL` selector constant).

---

## 4. The pattern, generalized

Every UI test in `tests/PersonTest.php` — and every UI test you write next —
is the same five moves in some order:

1. **Visit** — `$browser->visit($url)` — go somewhere.
2. **Find** — `$browser->find($selector)` — locate something on the page.
3. **Act** — `->fill(...)`, `->click()` — interact with what you found.
4. **Wait** — `$browser->waitFor($selector)` — give the server/page time to
   respond, and don't check anything until it has.
5. **Assert** — `$this->assertSame(...)`, etc. — state, in one line, exactly
   what "correct" looks like, so PHPUnit can tell you the instant it isn't.

If you're planning your next test (the roadmap in `HandoffPhase17.md` lists
booking conversion and guardian-invite acceptance as the next two), start by
writing out the plain-English version first — the numbered list a human
tester would follow — the same way section 1 of this document does. The PHP
falls out of that list almost mechanically once you know these five moves.

---

## 5. A second kind of test: running JavaScript in the page

Everything above uses one browser action per HTTP call — click, type, read
text. But some checks can't be expressed that way. "Is anything on this page
wider than the screen?" isn't a property of one element; it's a property of
the whole document. For those, WebDriver lets you run **arbitrary JavaScript
inside the page** and hand the result back to PHP.

`tests/AccessibilityAndViewportTest.php` is built on this. Two new
`lib/webdriver.php` methods make it work:

```php
// POST /session/<id>/execute/sync -- script returns a value directly
public function executeScript(string $script, array $args = []): mixed

// POST /session/<id>/execute/async -- script signals completion via callback
public function executeAsyncScript(string $script, array $args = []): mixed
```

**Why two?** Because of Promises. A synchronous script just returns:

```php
$width = $browser->executeScript('return window.innerWidth;');
```

But an *asynchronous* one — anything Promise-based — hasn't finished by the
time the function returns. So the async endpoint appends one extra argument
to your script: a **callback**. Your script must call it when the result is
ready:

```php
// arguments[0] here is the injected callback, because no $args were passed
$result = $browser->executeAsyncScript('axe.run().then(arguments[0]);');
```

If your script never calls that callback, the request hangs until
chromedriver's own script timeout fires. That's the one new failure mode
this style of test introduces.

### The mobile-viewport check

Resizing mid-test uses `POST /session/<id>/window/rect`:

```php
$browser->resizeWindow(375, 667);   // a small phone, in CSS pixels
```

This is different from the `--window-size=1280,900` flag passed at launch:
that only sets the *initial* size, whereas this can change size during a
test. Headless Chrome still has a real viewport size, and CSS media queries
respond to it exactly as they would on screen — going headless does not skip
responsive behaviour.

Then the overflow check, which is pure JavaScript:

```php
public function hasHorizontalOverflow(): bool
{
    return (bool) $this->executeScript(
        'return document.documentElement.scrollWidth > window.innerWidth;'
    );
}
```

`scrollWidth` is how wide the content actually is; `innerWidth` is how wide
the window is. If content exceeds window, something is forcing a horizontal
scrollbar — the classic mobile layout bug.

### The accessibility check

`runAxeScan()` does two things in sequence: inject the vendored
`assets/js/axe.min.js` (only if `window.axe` isn't already defined, so
repeated scans in one session don't re-inject 580KB), then run it:

```php
$this->executeScript($axeSource);                          // defines window.axe
$result = $this->executeAsyncScript('axe.run().then(arguments[0]);');
```

What comes back is a large JSON structure. The part that matters is
`violations`, a list of failed WCAG rules, each with:

- `id` — the rule name, e.g. `color-contrast`
- `impact` — `minor`, `moderate`, `serious`, or `critical`
- `help` — a human-readable description
- `nodes` — the specific offending elements, each with a CSS `target`
  selector and its `html`

### Two things this test does that the login test doesn't

**1. A severity threshold.** Only `serious` and `critical` fail the test.
`minor`/`moderate` findings are real but numerous on any first pass;
including them would make the test red for reasons unrelated to what it's
meant to catch first.

**2. A baseline of accepted violations.** The first run found real contrast
failures on all five pages (documented in `docs/ACCESSIBILITY_FINDINGS.md`).
Those specific elements are listed in `ACCEPTED_VIOLATIONS`, so the test is
green today and fails when something **new** breaks.

This deserves a word of caution, because a baseline is a sharp tool. A
careless one — "ignore `color-contrast` everywhere" — would hide every
future contrast bug. This one matches on **rule ID + exact selector**, so a
*different* element failing the same rule still fails the test.

That property was verified rather than assumed: removing `.badge-success`
from the baseline turned exactly one of the five tests red (`/dashboard`,
the only page with that element) and left the other four green. If it had
been blanket-forgiving by rule ID, nothing would have failed.

**A baseline is debt, not absolution.** The right lifecycle is: fix the CSS,
delete the entry, and from then on the test prevents the bug from returning.

### What Pass and Fail mean here

| Result | Meaning |
|---|---|
| **Pass** | At a 375px viewport the page has no horizontal overflow, the nav toggle is present, and axe found no serious/critical violations *other than* the known baselined ones. |
| **Fail (overflow)** | Something is wider than the phone viewport, forcing horizontal scroll. |
| **Fail (no toggle)** | `.navbar-toggler` is missing, so nav links are unreachable at that width. |
| **Fail (accessibility)** | A serious/critical violation appeared that isn't baselined — either genuinely new, or an existing issue whose selector changed. The message names the rule, the selector, and the element's markup. |

A failure message looks like this:

```
/dashboard has 1 unaccepted serious/critical accessibility violation(s).
These are NOT in self::ACCEPTED_VIOLATIONS, so they are either new or on a changed selector:
  - [color-contrast] Elements must meet minimum color contrast ratio thresholds (impact: serious)
      selector: .badge-success
      markup:   <span class="badge badge-success ml-2 flex-shrink-0" ...> ↑ Improving </span>
```

That's the whole point of wiring axe-core in rather than eyeballing pages:
it doesn't say "the dashboard has an accessibility problem," it names the
rule, the element, and the markup to change.

### Running it

```powershell
.\tools\chromedriver.exe --port=9515          # in one terminal, first
.\phpunit.cmd --filter AccessibilityAndViewportTest --testdox
.\phpunit.cmd --group accessibility            # just the a11y/viewport group
```
