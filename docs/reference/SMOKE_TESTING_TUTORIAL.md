# Smoke Testing Tutorial: Your First Click-Test

This is a beginner-level walkthrough of how `tests/smoke/` drives the **real
running application over HTTP** to catch whole-stack failures. It is the
companion to `docs/UI_TESTING_TUTORIAL.md` — where that one explains the
Selenium/Chrome browser tests, this one explains the lighter, faster
"click-test" tier that sits beside them.

Like that tutorial, it doesn't introduce new code. It takes the simplest
flow already in the harness — `tests/smoke/flows/01-auth-dashboard.ps1` — and
explains it three times, at increasing depth:

1. **Plain English** — what a human tester would do by hand.
2. **The code** — the exact PowerShell that performs each step, and what it
   does over the wire.
3. **Pass/fail breakdown** — what each check means, and what a failure of it
   would actually tell you.

If you understand this one flow, you understand every flow in `tests/smoke/`
— they're all built from the same five moves: **stage, request, read, check,
tear down.** (The first and last are done once by the runner; the middle
three are what each flow does.)

---

## 0. What a "click-test" is, and how it differs from the UI test

Both kinds of test exercise the real app, but they operate at different
layers and answer different questions:

| | **Smoke test (`tests/smoke/`)** | **UI test (`tests/PersonTest.php`)** |
|---|---|---|
| Drives | The HTTP stack: `php -S` serving your real routes/controllers/views | A real Chrome browser via chromedriver |
| Talks via | `curl.exe` sending HTTP requests, a cookie jar for the session | WebDriver → chromedriver → Chrome |
| Sees | The raw HTML/headers the server returns | The rendered, JavaScript-executed page |
| Speed | Fast (seconds) | Slower (a real browser per run) |
| Best at | "Does this page/route/POST work end-to-end without 500-ing? Did this write actually land in the DB?" | "Does this button, with its JS, behave correctly on screen at this viewport?" |
| Blind to | Anything that only happens in client-side JavaScript | — |

A click-test is a **smoke tier**: a wide, shallow, fast check that the real
request→controller→service→DB→response path is intact. It is not a
replacement for the browser tests; it's the cheaper net that catches the
class of bug the browser tests are too slow (and too narrow) to sweep for on
every change. Two real bugs were caught by exactly this technique during the
grade-resource and credit-checkout work: a live database that had never been
migrated (every assignment page was 500-erroring) and a grade-update bug that
the service unit tests were green on. Neither showed up in unit tests;
both showed up the instant a real HTTP request hit the running app.

---

## 1. The pieces that make this possible

The test flow itself never starts a server, copies a database, or cleans
anything up — a **runner** does all of that around it:

| File | Role |
|---|---|
| `tests/smoke/run-smoke.ps1` | The **runner**. Stages a disposable DB copy and temp uploads dir, migrates the copy, starts `php -S` pointed at them, runs every flow, and **always tears everything down**. One job: give each flow a clean, throwaway, real server to talk to — and leave no trace. |
| `tests/smoke/SmokeLib.ps1` | The **helper library**. Defines the five verbs a flow uses: `Smoke-Login`, `Smoke-Get`, `Smoke-PostForm`, `Smoke-Csrf`, `Smoke-Check`, plus cookie-jar management. A flow calls these; it never pokes at `curl` directly for the common cases. |
| `tests/smoke/flows/*.ps1` | The **flows**. One file per journey (`01-auth-dashboard.ps1`, `02-credit-checkout.ps1`, …). Each is dot-sourced by the runner in filename order and uses the `Smoke-*` helpers. This is where you add a new click-test. |

The chain of control: your flow calls `Smoke-Get`/`Smoke-PostForm`; those
call `curl.exe` with a cookie jar; curl sends a real HTTP request to
`http://127.0.0.1:<port>`; that's `php -S` running **your actual
`index.php`**, serving the same routing, controllers, services, and views a
real user hits — except pointed at a copy of the database, so nothing you do
can touch real data.

### The one thing that must be true before a flow can run: a disposable server

The UI test needs chromedriver started by hand first. The smoke runner is the
opposite — it starts (and stops) its own server automatically. But the
mechanism that makes that **safe** is worth naming, because it is the whole
reason this can be run repeatedly without fear:

```powershell
# inside run-smoke.ps1, abbreviated
Copy-Item $liveDb $tmpDb -Force                 # 1. disposable copy of the DB
New-Item -ItemType Directory -Path $tmpUp       # 2. temp uploads dir
& php database/migrate_production.php $tmpDb     # 3. migrate the COPY

$env:ATHLENE_DB_PATH    = $tmpDb                 # 4. point the app at the copy
$env:ATHLENE_UPLOAD_DIR = $tmpUp                 #    and the temp uploads dir
$srvProc = Start-Process php -ArgumentList '-S',"127.0.0.1:$Port",'index.php' ...
```

Those two environment variables are the safety seam. `Database::connect()`
reads `getenv('ATHLENE_DB_PATH') ?: (BASE_PATH . '/database/athlene.sqlite')`
— so when the var is set, the app opens the throwaway copy; when it's unset
(production, your normal dev server), it opens the real file exactly as
before. The uploads dir works the same way. **A smoke run can never write to
`database/athlene.sqlite` or `storage/uploads`**, because the server process
it talks to has those env vars pointed somewhere else entirely.

And the teardown is in a `finally` block, so it runs whether the flows pass,
fail, or throw:

```powershell
finally {
    if ($srvProc) { Stop-Process -Id $srvProc.Id -Force }
    Remove-Item env:ATHLENE_DB_PATH, env:ATHLENE_UPLOAD_DIR
    Remove-Item $tmpDb, $tmpUp -Recurse -Force      # the copy + temp uploads vanish
}
```

This is the smoke-test equivalent of the UI tutorial's point that "skipped is
not passed": a smoke run that crashes still cleans up after itself, so a
failed run never leaves a half-migrated copy or a stray server process
behind.

---

## 2. Plain English: what a human tester would do

Flow `01-auth-dashboard.ps1` answers the most basic question you can ask of a
web app: **"can people log in, and do the main pages come back without
erroring?"** By hand, a tester would:

1. Open the login page. Is there even a login form on it (a CSRF token proves
   the form rendered)?
2. Log in as a tutor with the known demo password.
3. Open the tutor dashboard. Did it come back as a real page — or a PHP
   `Fatal error` stack trace?
4. Open the assignments page. Same question. (This is the exact page that was
   silently 500-erroring before the live DB was migrated — which is *why*
   this check exists.)
5. Log out, log in as a student instead.
6. Open the student dashboard. Real page, no error?
7. Confirm the student actually sees their "Session Credits" card — i.e. the
   page didn't just avoid erroring, it rendered the content it's supposed to.

That's the whole flow. Nothing exotic — it's the five-minute click-around a
developer does by hand after a deploy to confirm "the lights are on." The
only difference is that each step below is a line of PowerShell instead of a
manual click, so it runs in seconds, every time, identically.

---

## 3. The code: overlaying PowerShell onto each step

Here is the real flow, from `tests/smoke/flows/01-auth-dashboard.ps1`, with
the human steps mapped on. The runner has already dot-sourced `SmokeLib.ps1`
and loaded `.env` credentials into `$Global:SmokeCred` before this runs.

```powershell
$tutorEmail = $Global:SmokeCred['TEST_TUTOR_EMAIL']
$tutorPass  = $Global:SmokeCred['TEST_TUTOR_PASSWORD']
$studEmail  = $Global:SmokeCred['TEST_STUDENT_EMAIL']
$studPass   = $Global:SmokeCred['TEST_STUDENT_PASSWORD']

# Step 1: the login page renders with a CSRF token
$login = Smoke-Get (Smoke-NewJar 'anon') '/login'
Smoke-Check ((Smoke-Csrf $login) -ne $null) "GET /login renders a CSRF token"

if ($tutorEmail -and $tutorPass) {
    $jar = Smoke-NewJar 'tutor'
    $ok = Smoke-Login $jar $tutorEmail $tutorPass          # Step 2: log in as tutor
    Smoke-Check $ok "tutor can log in"
    if ($ok) {
        $dash = Smoke-Get $jar '/dashboard'               # Step 3: tutor dashboard
        Smoke-Check ($dash -notmatch 'Fatal error|Uncaught') "tutor /dashboard renders without a PHP error"
        $asg = Smoke-Get $jar '/tutor/assignments'        # Step 4: assignments page
        Smoke-Check ($asg -notmatch 'Fatal error|Uncaught') "tutor /tutor/assignments renders without a PHP error"
    }
} else {
    Write-Host "  [skip] no TEST_TUTOR_* credentials in .env"
}

if ($studEmail -and $studPass) {
    $jar = Smoke-NewJar 'student'
    $ok = Smoke-Login $jar $studEmail $studPass            # Steps 5: log in as student
    Smoke-Check $ok "student can log in"
    if ($ok) {
        $dash = Smoke-Get $jar '/dashboard'               # Step 6: student dashboard
        Smoke-Check ($dash -notmatch 'Fatal error|Uncaught') "student /dashboard renders without a PHP error"
        Smoke-Check ($dash -match 'Session Credits') "student dashboard shows the Session Credits card"  # Step 7
    }
} else {
    Write-Host "  [skip] no TEST_STUDENT_* credentials in .env"
}
```

Walking through what each helper actually does over the wire:

### Step 1 — `Smoke-Get (Smoke-NewJar 'anon') '/login'`

`Smoke-NewJar` returns a fresh, unique path in the temp folder for curl's
**cookie jar** — the file where the session cookie will be stored and reused.
A new jar means a brand-new, logged-out session, the same as opening a
private browser window.

`Smoke-Get` is one line of curl:

```powershell
function Smoke-Get([string]$jar, [string]$path) {
    return Smoke-Str (curl.exe -s -b $jar "$Global:SmokeBase$path")
}
```

`curl.exe -s -b $jar <url>` does a `GET`, sending (`-b`) and the request is
silent (`-s`). `$Global:SmokeBase` is `http://127.0.0.1:<port>` — the
disposable server. The response body (the raw HTML the server produced) comes
back.

`Smoke-Str` exists because of a PowerShell gotcha worth internalizing: curl's
multi-line output arrives in PowerShell as an **array of lines**, not one
string. If you ran `-match` against it directly, you'd be matching line by
line and your `$Matches` could come back empty. `Smoke-Str` joins the lines
back into one string first. (This is the smoke-harness equivalent of the UI
test's "re-find the element, don't reuse a stale handle" — a small
environment truth you only learn by being bitten once.)

### Step 1 check — `Smoke-Csrf $login`

```powershell
function Smoke-Csrf($html) {
    $s = Smoke-Str $html
    if ($s -match 'name="csrf_token"\s+value="([^"]+)"') { return $Matches[1] }
    return $null
}
```

It scrapes the hidden CSRF field out of the returned HTML. If it finds one,
the login form rendered. If `$null` comes back, the page didn't render a form
at all — either the route is broken or the server returned an error page.

### Step 2 — `Smoke-Login $jar $tutorEmail $tutorPass`

This is the one helper that does more than a single request, because logging
in is a two-step dance — exactly what a browser does:

```powershell
function Smoke-Login([string]$jar, [string]$email, [string]$password) {
    $login = Smoke-Str (curl.exe -s -c $jar "$Global:SmokeBase/login")   # 1. GET the form
    $t = Smoke-Csrf $login                                               #    scrape its token
    if (-not $t) { return $false }
    curl.exe -s -b $jar -c $jar -L -o $out `                             # 2. POST credentials
        --data-urlencode "csrf_token=$t" `
        --data-urlencode "email=$email" `
        --data-urlencode "password=$password" "$Global:SmokeBase/login" | Out-Null
    $body = Get-Content $out -Raw
    return ($body -notmatch 'name="email"')
}
```

First it `GET`s `/login` to obtain a valid CSRF token (`-c $jar` *writes* the
session cookie to the jar). Then it `POST`s the credentials back — `-b $jar`
*reads* that cookie so the server recognizes the same session, `-L` follows
the redirect the app issues on success, and `--data-urlencode` sends the
fields exactly as the HTML form would.

The return value is the clever, simple part: **"did we end up back on the
login form?"** After a successful login the app redirects to a dashboard,
whose HTML has no `name="email"` field; after a failed one it re-renders the
login form, which does. So `-notmatch 'name="email"'` is a reliable
"logged-in?" signal without needing to know what the dashboard looks like.

### Steps 3, 4, 6 — `Smoke-Get $jar '/dashboard'` etc.

Each is one authenticated `GET` (the jar now carries the logged-in session
cookie). The check is deliberately blunt:

```powershell
Smoke-Check ($dash -notmatch 'Fatal error|Uncaught') "tutor /dashboard renders without a PHP error"
```

It doesn't verify the dashboard is *correct* — it verifies the page didn't
blow up with a PHP `Fatal error` or `Uncaught` exception. That is exactly the
bug class smoke tests exist to catch: the whole page 500-ing because of a
missing DB column, an unwired service, a bad `require`. A browser test would
also catch this, but far more slowly, and you'd typically only point one at a
couple of pages — the smoke flow can sweep every important route in seconds.

### Step 7 — `$dash -match 'Session Credits'`

This one goes a step further than "didn't error": it confirms the page
rendered the content it's supposed to. A page can return HTTP 200 with no PHP
error and still be missing a whole section (e.g. a controller silently passed
an empty array to the view). Matching on a known piece of expected copy —
"Session Credits" — is a cheap way to assert "the thing the user came for is
actually here," without the fragility of asserting the whole page.

### `Smoke-Check` — recording a result

```powershell
function Smoke-Check([bool]$cond, [string]$label) {
    $Global:SmokeTotal++
    if ($cond) { Write-Host "  [PASS] $label" }
    else { Write-Host "  [FAIL] $label" -ForegroundColor Red; $Global:SmokePass = $false; $Global:SmokeFailed++ }
}
```

Every check is one `Smoke-Check`: a boolean and a human-readable label. It
prints `[PASS]`/`[FAIL]`, tallies the totals, and — crucially — flips
`$Global:SmokePass` to `$false` on any failure. The runner reads that global
at the end to decide the overall result and the process exit code.

---

## 4. What Pass and Fail actually mean

Unlike a PHPUnit method (which stops at the first failed assertion), a smoke
flow runs **every** `Smoke-Check` regardless of earlier failures — so one run
tells you *all* the checks that are currently broken, not just the first. The
runner aggregates them.

Here is what each check in this flow is really asserting, and what a failure
would tell you:

| Check | Verifies | If it PASSES | If it FAILS |
|---|---|---|---|
| `GET /login renders a CSRF token` | The login route returns a real form | The front door renders | The `/login` route is broken or erroring before the form — nothing else in the flow can be trusted |
| `tutor can log in` | A known-good credential completes the GET-token → POST → redirect dance | Auth + session cookies work end-to-end over HTTP | Either the credential is wrong, the session cookie isn't being set/read, or the login POST is erroring |
| `tutor /dashboard renders without a PHP error` | The page has no `Fatal error`/`Uncaught` | The tutor dashboard's whole render path is intact | A 500 — commonly a missing DB column, unwired service, or bad `require`; the exact class of bug a migrated-vs-code mismatch produces |
| `tutor /tutor/assignments renders without a PHP error` | Same, for the assignments page | The assignments grid renders for a real tutor | This is the canary that caught the un-migrated-DB bug: `no such column: ag.resource_post_id` 500'd this page while unit tests stayed green |
| `student can log in` | Student-role auth works | — | As above, for a student account |
| `student /dashboard renders without a PHP error` | Student dashboard render path intact | — | A 500 on the student's main page |
| `student dashboard shows the Session Credits card` | The expected content is present, not just absent errors | The page rendered its intended section | HTTP 200 but the credits card is missing — a controller/view wiring gap that an error-only check would miss |

A `[skip]` line (not a pass or a fail) appears when a `.env` credential is
absent — the same philosophy as the UI test's `markTestSkipped`: "this check
didn't get a chance to run," distinct from passing. The flow stays green but
you were told it was skipped.

The runner's final line is the verdict:

```
SMOKE PASSED  (20 checks)
```

or, if anything failed:

```
SMOKE FAILED  (2/20 checks failed)
```

and the runner `exit 1`s, so a CI step or a pre-push hook can gate on it.

### An example failure, read end to end

Suppose someone edits a controller and forgets to pass the data the view
needs, so `/tutor/assignments` throws. The run would print:

```
Flow: 01-auth-dashboard
  [PASS] GET /login renders a CSRF token
  [PASS] tutor can log in
  [PASS] tutor /dashboard renders without a PHP error
  [FAIL] tutor /tutor/assignments renders without a PHP error
  ...
SMOKE FAILED  (1/20 checks failed)
```

That `[FAIL]` line names the exact route that broke. The next move is to open
that URL in a browser against your dev server (or read the server log the
runner captured) to see the actual stack trace — the smoke test's job was to
*find* the broken page fast and unambiguously, not to diagnose it.

---

## 5. The pattern, generalized

Every flow in `tests/smoke/` is the same shape. Three of the five moves are
done once by the runner; you only write the middle three:

1. **Stage** *(runner)* — disposable DB copy + temp uploads + a real server,
   via the `ATHLENE_DB_PATH` / `ATHLENE_UPLOAD_DIR` seam.
2. **Request** — `Smoke-Login`, `Smoke-Get`, `Smoke-PostForm`, or a raw
   `curl.exe -F ...` for multipart file uploads — act as a real user would.
3. **Read** — capture the returned HTML/redirect/body as a string.
4. **Check** — `Smoke-Check <bool> "<label>"` — one line stating what
   "correct" looks like.
5. **Tear down** *(runner)* — stop the server, delete the copy + temp dir, in
   a `finally` so it always happens.

When you plan a new flow, do what the UI tutorial recommends: **write the
plain-English numbered list first** — the click-around a human would do — then
map each step onto a `Smoke-Get`/`Smoke-PostForm` + a `Smoke-Check`. The
credit-checkout flow (`02-credit-checkout.ps1`) is the worked example of a
*multi-actor, state-changing* flow built exactly this way — see §6.

To add it, drop a `tests/smoke/flows/NN-name.ps1` file (the `NN` prefix sets
run order). It is dot-sourced automatically; no runner change is needed. Use
`$Global:SmokeCred['TEST_*']` for credentials and self-skip (a `[skip]` line
+ `return`) if a needed one is absent, so the harness still runs cleanly on a
machine without those demo accounts.

---

## 6. A second kind of flow: multi-actor, state-changing, with a file upload

Flow `01` only *reads* pages. The credit-checkout flow
(`02-credit-checkout.ps1`) is the more powerful kind: it has **two actors**
(a student who buys, an admin who confirms), it **changes state** (a real
purchase request, a real credit grant), and it **uploads a file** (proof of
payment). It's the smoke-test proof that an entire feature works through the
real stack. Three things it does that flow `01` doesn't:

### 1. A real multipart file upload

`Smoke-Get`/`Smoke-PostForm` send urlencoded forms. A file upload needs
multipart, which `curl.exe -F` does natively — so the flow drops to raw curl
for that one request:

```powershell
curl.exe -s -b $sjar -c $sjar -L -o $out `
    -F "csrf_token=$bt" -F "credit_package_id=$pkgId" -F "method=bank_transfer" `
    -F "reference=$ref" -F "proof[]=@$proof;type=image/png" "$Global:SmokeBase/credits/buy"
```

`-F "proof[]=@$proof;type=image/png"` attaches an actual PNG file from disk as
the `proof[]` form field — the exact same multipart POST a real browser sends
when a parent uploads a deposit screenshot. This is the one move that genuinely
can't be faked at a lower layer: it proves the upload is validated, stored, and
linked all the way through.

(On Windows PowerShell 5.1 this must be `curl.exe -F`, **not** PowerShell's
`Invoke-WebRequest`, which has no `-Form` parameter — one of the harness
gotchas recorded in `SmokeLib.ps1`.)

### 2. Two sessions in one flow

Each actor gets their own cookie jar — `Smoke-NewJar 'cc_student'` and
`Smoke-NewJar 'cc_admin'` — so the flow holds two independent logged-in
sessions at once and switches between them, exactly as if two people were at
two computers. The student submits the request under their jar; the admin
confirms it under theirs.

### 3. A correlating token to prove the write landed

Before buying, the flow generates a unique reference string:

```powershell
$ref = 'SMOKE-' + [guid]::NewGuid().ToString('N').Substring(0,8)
```

It submits that as the payment reference, then — logged in as the admin —
checks that the reference **appears** in the review queue (`-match`), confirms
the request, and checks the reference **no longer appears** (`-notmatch`,
because confirmed requests leave the pending queue). That before/after pair is
how a read-only HTTP check proves a *state change* actually happened: the same
unique token being present, then absent, is unforgeable evidence that the
confirm really moved the request — not just that a page returned 200.

Its plain-English version, the way §2 does it:

1. Student logs in, opens `/credits` — are packages and the CIBC payment
   instructions shown?
2. Student opens a package's buy form — is it a multipart form?
3. Student submits a purchase request with a unique reference and a real proof
   image — accepted, and shown as "Pending"?
4. Admin logs in, opens `/credits/review` — is that student's request visible,
   with its proof served?
5. Admin confirms it — does it succeed, and does the request then leave the
   pending queue?

Each line became one `curl` + one `Smoke-Check`. Latest run: all checks green,
20 across both flows.

---

## 7. Running it

One command — the runner handles the server, the DB copy, and cleanup:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File tests/smoke/run-smoke.ps1
```

Optional: a different port if 8097 is busy —

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File tests/smoke/run-smoke.ps1 -Port 8099
```

What you need in place first:
- **`php` and `curl.exe` on `PATH`** (both ship with the standard Windows PHP
  + Windows 10/11 setup this project already uses).
- **`.env` with the `TEST_*` demo credentials** (`TEST_TUTOR_*`,
  `TEST_STUDENT_*`, `TEST_ADMIN_*`) — the same seeded accounts the Selenium
  suite uses. Flows self-skip the parts they can't run without a given
  credential, so a partial `.env` still produces a clean run.

What you do **not** need: to start a server, to migrate anything, or to clean
up afterward. The runner copies and migrates a throwaway DB, starts and stops
its own `php -S`, and deletes every temp artifact in a `finally` block.

### How this relates to the other test tiers

| Command | Tier | Speed | Catches |
|---|---|---|---|
| `.\phpunit.cmd` | Unit / service (in-memory SQLite) | Fastest | Logic bugs in a single service/method |
| `tests/smoke/run-smoke.ps1` | HTTP smoke (real server, disposable DB) | Fast | Whole-stack breakage: routes, controllers, migrations, uploads, 500s |
| `.\phpunit.cmd --group ui` (+ chromedriver) | Browser (real Chrome) | Slowest | Rendered-page + JavaScript + responsive behaviour |

Run the smoke tier after any change that touches routing, a controller, the
schema/migration, or a file-upload path — it is the fastest way to confirm the
real app still serves the pages a user actually hits. It does not replace the
unit tests below it or the browser tests above it; it fills the gap between
them, which is exactly where the two migration/logic bugs that motivated it
were hiding.
