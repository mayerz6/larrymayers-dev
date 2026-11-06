# PHPUnit Tutorial: From a Pure Function to a Full User Journey

This walks through the two test tiers now in `tests/` (`tests/Unit` and
`tests/Smoke`), in the same three-pass style as `docs/reference/*` uses for
the Athlene project: **plain English**, **the code**, **what pass/fail
actually mean**. Those two reference files are worth reading once you're
comfortable here -- the underlying ideas (explicit waits, disposable
databases, before/after proof) are the same ones Playwright testing will use
later. This document builds the same muscle with PHPUnit first, because it
needs no browser, no driver process, and runs in about three seconds.

If you understand the one "journey" test walked through in Part 2, you have
the pattern for every future smoke test on this platform -- and, later, for
every Playwright test too. They're the same five moves wearing different
clothes.

---

## Part 0: The two tiers, and why they're separate files

| | `tests/Unit` | `tests/Smoke` |
|---|---|---|
| What it calls | One PHP function directly | The real app over real HTTP |
| Needs a server? | No | Yes -- `php -S`, started by the test itself |
| Needs a database? | No | Yes -- a fresh disposable SQLite file per run |
| Speed | Milliseconds | ~1-3 seconds (server startup dominates) |
| Answers | "Does this one piece of logic do the right thing for every input I can think of?" | "If a real admin did this whole task through the real app, would it actually work -- routing, auth, database, and all?" |

Both tiers run with one command because `phpunit.xml` defines two
`<testsuite>` blocks pointing at the two folders:

```powershell
php tools\phpunit.phar --testdox           # everything
php tools\phpunit.phar --testsuite Unit    # just the fast one
php tools\phpunit.phar --testsuite Smoke   # just the HTTP one
```

`tests/bootstrap.php` runs once before any test, and only loads what the
**Unit** suite needs (`Database.php` for the class definition, `Response.php`,
and the two controllers that hold `normalizeDuties()`/`normalizeTechnologies()`).
The Smoke suite never uses that bootstrap's requires at all -- it talks to a
separate, fully independent PHP process over a socket, exactly like a real
browser would.

---

## Part 1: The simplest kind of test -- a pure function

Before the multi-step journey test, it's worth seeing the smallest possible
unit, because every other test is built from this same shape.

`normalizeDuties()` lives in `larrymayers-app/src/Controllers/ResumeController.php`.
It takes whatever a human typed into the "Duties" textarea and turns it into
a clean list, one entry per line, no blank lines, no stray whitespace.

**Plain English:** "If I give this function three lines of text with some
blank lines and extra spaces thrown in, I expect back exactly three clean
strings, in the same order, with nothing extra."

**The code** (`tests/Unit/NormalizeDutiesTest.php`):

```php
public function testDropsBlankLinesBetweenRealLines(): void
{
    $raw = "First duty\n\n\nSecond duty\n   \nThird duty";

    $result = normalizeDuties($raw);

    $this->assertSame(
        ['First duty', 'Second duty', 'Third duty'],
        $result
    );
}
```

Three lines, no setup, no teardown. `assertSame()` checks both *value and
type* match exactly (`assertEquals()` would accept looser matches -- for
exact arrays like this, `assertSame()` is the stricter and more honest
choice).

**Pass/fail, concretely:**

| Result | Meaning |
|---|---|
| **Pass** | For this input, the function returns exactly `['First duty', 'Second duty', 'Third duty']` |
| **Fail** | PHPUnit prints the expected array next to the actual array, line by line, e.g. if someone changed the regex and a blank entry started leaking through: `Failed asserting that two arrays are identical. --- Expected +++ Actual @@ @@  Array ( 0 => 'First duty' 1 => '' 2 => 'Second duty' ...` |

That diff is the entire value of the test: it doesn't just say "normalize
broke," it shows you exactly which element is wrong.

One pattern worth internalizing from `NormalizeDutiesTest`'s other methods:
each test method asks exactly **one** question. `testEmptyStringProducesAnEmptyArray`,
`testWindowsLineEndingsAreHandled`, `testOldMacStyleCarriageReturnOnlyLineEndingsAreHandled`
-- each is a single input/output pair, named after the question it answers.
When a test fails, the method name alone tells you which specific behavior
broke, before you even read the diff.

### The one gotcha unit tests have to manage: shared state

`ResponseHelperTest` tests `isAjaxRequest()`, which reads `$_SERVER['HTTP_X_REQUESTED_WITH']`.
Because that's a superglobal, not a function argument, PHPUnit won't reset it
between test methods on its own:

```php
protected function setUp(): void
{
    $this->originalHeaderValue = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? null;
}

protected function tearDown(): void
{
    if ($this->originalHeaderValue === null) {
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    } else {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = $this->originalHeaderValue;
    }
}
```

`setUp()` runs before *every* test method in the class; `tearDown()` runs
after *every* one, pass or fail. This is the smallest possible preview of
exactly the same idea the Smoke tier uses at a much bigger scale: **whatever
a test changes, something must put back or throw away, and that something
must run even when the test fails.**

---

## Part 2: The full user journey -- `ResumeJourneyTest`

This is the test worth studying closely, because it's the direct PHPUnit
equivalent of the Selenium login test in `docs/reference/UI_TESTING_TUTORIAL.md`
-- same five moves, same insistence on proving things rather than assuming
them, just driving real HTTP requests instead of a real browser.

### Step 0: the two pieces that make it possible

| File | Role |
|---|---|
| `tests/Smoke/Support/SmokeServer.php` | Builds a disposable SQLite database, starts a real `php -S` process pointed at it, and guarantees it's torn down afterward. |
| `tests/Smoke/Support/HttpClient.php` | A thin wrapper over PHP's `curl_*` functions. One instance = one browser session (its own cookie jar). |

Neither file is a "test" (PHPUnit only auto-discovers files whose classes
extend `TestCase`), so each Smoke test file `require_once`s them explicitly
at the top.

### Step 1: plain English

Imagine being asked to manually verify "can the admin add a new resume entry,
and does it actually show up on the public site?" You'd do this:

1. As a random visitor (not logged in), open the public `/resume` page. Make
   a mental note: the entry you're about to create isn't there yet.
2. Log in as the admin.
3. Fill out and submit the "add resume entry" form -- title, company,
   summary, years, and a couple of duties.
4. Check that the admin's own "manage resume" screen now lists the new
   entry.
5. **Go back to the public `/resume` page -- the same one from step 1, as
   the same kind of visitor, not logged in** -- and confirm the new entry is
   there too. This is the step that actually matters: step 4 only proves the
   admin's screen thinks it worked. Step 5 proves the data really landed in
   the database and is visible to the public.
6. As a bonus check, look in the database directly to confirm the "duties"
   you typed were saved as separate rows, correctly linked to the new entry.

That's the whole test. Six numbered steps, the same thing a developer would
click through by hand after deploying a change to the resume feature.

### Step 2: the code, mapped to each step

From `tests/Smoke/ResumeJourneyTest.php`:

```php
$uniqueTitle = 'Smoke Journey Engineer ' . bin2hex(random_bytes(4));

// --- Step 1 ---
$anonymous = new HttpClient($this->server->baseUrl());
$before = $anonymous->get('/resume');
$this->assertStringNotContainsString($uniqueTitle, $before['body']);

// --- Step 2 ---
$admin = new HttpClient($this->server->baseUrl());
$admin->post('/login', [
    'email' => SmokeServer::TEST_ADMIN_EMAIL,
    'password' => SmokeServer::TEST_ADMIN_PASSWORD,
]);

// --- Step 3 ---
$create = $admin->post('/resume/create', [
    'title' => $uniqueTitle,
    'company' => 'Smoke Test Industries',
    'summary' => 'Created end-to-end by ResumeJourneyTest.',
    'start_year' => '2022',
    'end_year' => '2024',
    'duties' => "Wrote automated tests\nKept production stable",
]);
$this->assertSame(302, $create['status']);
$this->assertSame('/resume/manage', $admin->lastHeader('Location'));

// --- Step 4 ---
$manage = $admin->get('/resume/manage');
$this->assertStringContainsString($uniqueTitle, $manage['body']);

// --- Step 5 ---
$after = $anonymous->get('/resume');
$this->assertStringContainsString($uniqueTitle, $after['body']);

// --- Step 6 ---
$directDb = $this->server->connectDirectly();
$duties = $directDb->prepare("SELECT duty FROM duties d JOIN resume r ON r.id = d.resume_id WHERE r.title = :title ORDER BY d.order_index")
    ->execute([':title' => $uniqueTitle]) ... // fetchAll(PDO::FETCH_COLUMN)
$this->assertSame(['Wrote automated tests', 'Kept production stable'], $duties);
```

Walking through a few details worth understanding, not just copying:

**Why a random suffix on the title?** `bin2hex(random_bytes(4))` makes a
title that has never existed before and never will again. That's what makes
`assertStringNotContainsString()` in step 1 and `assertStringContainsString()`
in step 5 trustworthy -- there's no way to pass those assertions by
accident, the way you could if the test used a fixed title like "Software
Engineer" that might coincidentally already be seeded somewhere. This is the
exact same idea as the PowerShell tutorial's `SMOKE-<guid>` payment
reference, just applied to a title string.

**Why two separate `HttpClient` instances (`$anonymous` and `$admin`)?**
Each `HttpClient` has its own cookie jar file. `$anonymous` never logs in, so
it never carries a session cookie -- it's a faithful stand-in for "a random
visitor," reused in both step 1 and step 5 specifically so that both checks
are using the exact same kind of request.

**Why check `Location: /resume/manage` instead of just the status code?**
`assertSame(302, ...)` alone would also pass if the app redirected somewhere
completely wrong, like back to the login page because the session silently
expired. Checking *both* the status code and where it redirects *to* is the
difference between "something happened" and "the specific right thing
happened."

**Why query the database directly in step 6, instead of just trusting the
HTTP response?** `$create`'s JSON/redirect only proves the *server said* it
worked. Re-reading the page in step 4/5 proves the write is visible through
the app's own rendering -- but that still goes through the same `view()`/PDO
code path that did the write, so in principle a bug could make both "look"
consistent while still being wrong. A direct `SELECT` against the SQLite
file, using a brand-new `PDO` connection the app itself never touches, is the
most independent check available. This is the same reasoning the PowerShell
tutorial applies when it checks a reference string's presence/absence in the
admin queue rather than only trusting a "success" response.

### Step 3: what pass and fail actually mean here

| Assertion | If it PASSES | If it FAILS |
|---|---|---|
| Step 1: entry absent beforehand | Confirms the test starts from a clean slate | Means the disposable database isn't actually fresh -- a setup bug, not an app bug |
| Step 3: `302` + `Location: /resume/manage` | The create form's happy path works exactly as coded | Either validation rejected valid input, an exception was thrown and caught (redirecting elsewhere), or the redirect target changed |
| Step 4: admin's own list shows it | The manage screen's query/render path works | The INSERT didn't happen, or `fetchResumeEntries()`/the view stopped rendering new rows |
| Step 5: public page shows it | The full loop -- write, then public read -- works end to end | This is the one that would have caught a bug where admin and public pages read from different tables/connections, or a caching layer silently served a stale page |
| Step 6: duties saved correctly, in order | `saveResumeDuties()`'s loop and `order_index` logic are intact | Either no duties were saved, they're out of order, or (if someone reintroduces the historic `project_technologies` bind-order bug from the handoff notes) a column got the wrong value |

A **failure** here names the exact step that broke, with PHPUnit's usual
expected/actual diff. An **error** (not a failure) -- e.g. a `PDOException`
instead of a failed `assertSame` -- would mean something broke before an
assertion even got a chance to run, most likely the schema or the disposable
server itself, the test-infrastructure equivalent of chromedriver crashing
mid-test in the UI tutorial.

---

## Part 4: The pattern, generalized

Every Smoke test in this suite, present and future, is the same five moves --
notice this is the *same shape* as both reference tutorials' patterns, just
worded for HTTP instead of a browser or PowerShell:

1. **Stage** -- `SmokeServer::start()` -- a disposable database + a real
   `php -S` process. Done once per class (`AuthRoutesTest`, cheap read-only
   checks) or once per method (`ResumeJourneyTest`, anything that writes
   data) -- see each class's docblock for which, and why.
2. **Act** -- `$client->get(...)` / `$client->post(...)` -- do exactly what a
   real browser would send.
3. **Read** -- capture the status code, body, and headers of the response.
4. **Check** -- one `assertSame`/`assertStringContainsString`/etc. per fact
   that has to be true. State it narrowly enough that a failure tells you
   exactly what broke.
5. **Tear down** -- `SmokeServer::stop()`, in `tearDown()`/`tearDownAfterClass()`
   so it always runs.

When you plan the next one -- and you will, every time a new controller or
form is added -- write the plain-English numbered list first, the way Part 2
above does, *before* writing any PHP. The code falls out of that list almost
mechanically once each step is one `HttpClient` call plus one assertion.

**What this tier deliberately does not check:** anything that only happens
inside the browser after the HTML arrives -- JavaScript-driven UI behavior,
the hamburger menu's mobile collapse, `app.js`'s async form submission
feedback, actual rendered layout at a given screen width. Those gaps are
exactly what Playwright is for, and the five-move pattern above is already
your mental model for it: "stage" becomes `page.goto()`, "act" becomes
`page.click()`/`page.fill()`, "check" becomes `expect(...)`. You're not
starting over when that work begins -- you're applying the same shape to a
tool that can also see the rendered page.

---

## Running it

```powershell
php tools\phpunit.phar --testdox             # everything, with readable names
php tools\phpunit.phar --testsuite Unit      # fast tier only
php tools\phpunit.phar --testsuite Smoke     # HTTP tier only
php tools\phpunit.phar --filter ResumeJourneyTest   # just the journey test
```

No chromedriver, no separate runner script, no manual server start/stop --
`SmokeServer` does that inside PHPUnit's own `setUp`/`tearDown` hooks. That's
the one structural difference from the PowerShell smoke harness worth
naming: there, a separate `run-smoke.ps1` process managed staging and
teardown around the test flows. Here, PHPUnit's own lifecycle methods do
that job, so there's one fewer moving part and one fewer language involved.

### A note on PHPUnit versions

`tools/` has two phars: `phpunit.phar` (11.5.55, works with the local PHP
8.2.12) and `phpunit-v13.phar` (requires PHP >= 8.4.1, which isn't installed
here -- confirmed by running `--version` on each). Use `phpunit.phar` for
now; revisit the newer one if/when the local PHP version is upgraded.
