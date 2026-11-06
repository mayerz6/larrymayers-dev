<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/SmokeServer.php';
require_once __DIR__ . '/Support/HttpClient.php';

/**
 * This is the "user journey" test: one method that plays out the entire
 * real-world story end to end, the same way docs/PHPUNIT_TUTORIAL.md walks
 * through it line by line. AuthRoutesTest asks narrow yes/no questions
 * ("does this one route reject anonymous access?"); this class asks "if a
 * real admin sat down and did this whole task, would it actually work, and
 * would the result actually show up where a real visitor would see it?"
 *
 * Unlike AuthRoutesTest, this gets its OWN fresh server + fresh database per
 * test method (setUp/tearDown, not setUpBeforeClass/tearDownAfterClass).
 * That costs a little time, but it buys something worth it here: every test
 * method in this class writes data (creates a resume entry), and starting
 * clean means each one can assert "the resume table has exactly 1 row" or
 * "this exact title appears on the public page" without worrying about
 * leftovers from a previous test method polluting the count. The alternative
 * (one shared server, carefully-unique titles, manual cleanup at the end of
 * every test) is exactly the kind of inter-test coupling that makes test
 * suites fragile -- paying a few hundred milliseconds of server startup to
 * avoid it is the right trade here.
 */
final class ResumeJourneyTest extends TestCase
{
    private SmokeServer $server;

    protected function setUp(): void
    {
        $this->server = new SmokeServer();
        $this->server->start();
    }

    protected function tearDown(): void
    {
        // Runs even if an assertion above failed -- the disposable database
        // file is deleted and the php -S process is killed either way. This
        // is what guarantees a failed run never leaves a stray server
        // listening on a port or a temp .sqlite file behind.
        $this->server->stop();
    }

    public function testAdminCanPublishAResumeEntryAndItAppearsOnThePublicPage(): void
    {
        // A random suffix means this test can never accidentally pass by
        // matching leftover data from a different run -- the exact same
        // technique the PowerShell tutorial uses with its SMOKE-<guid>
        // payment reference, applied here to a resume title instead.
        $uniqueTitle = 'Smoke Journey Engineer ' . bin2hex(random_bytes(4));

        // --- Step 1: a stranger looks at the public resume page first ---
        $anonymous = new HttpClient($this->server->baseUrl());
        $before = $anonymous->get('/resume');
        $this->assertSame(200, $before['status']);
        $this->assertStringNotContainsString(
            $uniqueTitle,
            $before['body'],
            'The entry must not exist before this test creates it.'
        );

        // --- Step 2: the admin logs in ---
        $admin = new HttpClient($this->server->baseUrl());
        $login = $admin->post('/login', [
            'email' => SmokeServer::TEST_ADMIN_EMAIL,
            'password' => SmokeServer::TEST_ADMIN_PASSWORD,
        ]);
        $this->assertSame(200, $login['status']);

        // --- Step 3: the admin submits the resume-create form ---
        // No X-Requested-With header is sent, on purpose: this app's forms
        // are meant to work without JavaScript (see PROJECT_CONTEXT.md's
        // "progressive enhancement" rule), so the plain-form path through
        // resumeCreate() -- flash message + redirect, not a JSON response --
        // is the path this test exercises.
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

        // --- Step 4: the admin's own manage screen shows the new entry ---
        $manage = $admin->get('/resume/manage');
        $this->assertSame(200, $manage['status']);
        $this->assertStringContainsString($uniqueTitle, $manage['body']);

        // --- Step 5: the public page -- as a stranger, same as step 1 --
        // now shows it too. This is the step that actually proves the write
        // landed in the database and is live, not just that the admin's own
        // screen (which could theoretically show stale session data) says so.
        $after = $anonymous->get('/resume');
        $this->assertSame(200, $after['status']);
        $this->assertStringContainsString(
            $uniqueTitle,
            $after['body'],
            'A newly-created resume entry must render on the public /resume page.'
        );

        // --- Step 6: confirm the duties were actually saved as child rows ---
        // (a direct DB check, not an HTTP one -- see connectDirectly()'s
        // docblock for why this kind of check is worth having alongside the
        // HTTP-level assertions above)
        $directDb = $this->server->connectDirectly();
        $stmt = $directDb->prepare(
            "SELECT duty FROM duties d
             JOIN resume r ON r.id = d.resume_id
             WHERE r.title = :title
             ORDER BY d.order_index ASC"
        );
        $stmt->execute([':title' => $uniqueTitle]);
        $duties = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->assertSame(['Wrote automated tests', 'Kept production stable'], $duties);

        $anonymous->close();
        $admin->close();
    }
}
