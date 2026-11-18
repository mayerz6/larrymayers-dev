<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/SmokeServer.php';
require_once __DIR__ . '/Support/HttpClient.php';

/**
 * Regression coverage for the Resume/Projects accordion markup, added
 * after a real incident: the accordion's actual *behavior* broke twice in
 * one session (invisible text from a CSS cascade conflict, then a dead
 * click handler after the app's SPA content-swap) while the underlying
 * server-rendered HTML stayed completely correct both times.
 *
 * Be clear-eyed about what this class can and cannot catch. cURL has no
 * browser engine behind it -- it cannot compute a CSS cascade and cannot
 * execute JavaScript, so neither of the two bugs above would have been
 * caught by this file even if it had existed before they happened. See
 * tests/e2e/accordion.spec.js (Playwright) for the layer that actually
 * covers that ground, and CONSULTING_ROADMAP_HANDOFF.md / this project's
 * chat history for the fuller discussion of why both layers are needed.
 *
 * What this class is genuinely good for: proving the server-rendered
 * markup both the CSS and JS layers depend on doesn't silently disappear
 * or change shape in a future edit -- the right <details>/<summary>
 * structure, the right classes, and (the thing that used to be invisible
 * on the public page entirely, before this session's work) that duties
 * and technologies actually render inside the expandable body.
 */
final class AccordionMarkupTest extends TestCase
{
    private static SmokeServer $server;

    public static function setUpBeforeClass(): void
    {
        self::$server = new SmokeServer();
        self::$server->start();
    }

    public static function tearDownAfterClass(): void
    {
        self::$server->stop();
    }

    private function login(HttpClient $client): void
    {
        $response = $client->post('/login', [
            'email' => SmokeServer::TEST_ADMIN_EMAIL,
            'password' => SmokeServer::TEST_ADMIN_PASSWORD,
        ]);
        $this->assertSame(200, $response['status'], 'Admin login must succeed before creating fixture data.');
    }

    public function testAResumeEntryRendersAsADetailsAccordionWithItsDutiesVisible(): void
    {
        $uniqueTitle = 'Accordion Markup Check ' . bin2hex(random_bytes(4));

        $admin = new HttpClient(self::$server->baseUrl());
        $this->login($admin);

        $create = $admin->post('/resume/create', [
            'title' => $uniqueTitle,
            'company' => 'Markup Test Co',
            'summary' => 'Created to check the accordion markup renders correctly.',
            'start_year' => '2020',
            'end_year' => '2021',
            'duties' => "Checked the accordion renders\nConfirmed duties are visible",
        ]);
        $this->assertSame(302, $create['status']);

        $anonymous = new HttpClient(self::$server->baseUrl());
        $response = $anonymous->get('/resume');

        $this->assertSame(200, $response['status']);
        $body = $response['body'];

        $this->assertStringContainsString($uniqueTitle, $body);
        $this->assertStringContainsString('class="resume-feed"', $body);
        $this->assertStringContainsString('<details class="resume-entry">', $body);
        $this->assertStringContainsString('class="resume-entry-body"', $body);
        $this->assertStringContainsString('class="resume-chevron"', $body);

        // The specific gap that motivated building this page this way:
        // duties already existed in the database and were editable in the
        // admin area, but resume.php used to never render them publicly
        // at all. If a future edit drops the duties loop again, this is
        // what catches it.
        $this->assertStringContainsString('Checked the accordion renders', $body);
        $this->assertStringContainsString('Confirmed duties are visible', $body);

        $admin->close();
        $anonymous->close();
    }

    public function testAProjectEntryRendersAsADetailsAccordionWithItsTechnologiesVisible(): void
    {
        $uniqueTitle = 'Accordion Project Check ' . bin2hex(random_bytes(4));

        $admin = new HttpClient(self::$server->baseUrl());
        $this->login($admin);

        $create = $admin->post('/projects/create', [
            'title' => $uniqueTitle,
            'description' => 'Created to check the project accordion markup renders correctly.',
            'link' => 'https://example.test/markup-check',
            'technologies' => "PHPUnit\ncURL",
        ]);
        $this->assertSame(302, $create['status']);

        $anonymous = new HttpClient(self::$server->baseUrl());
        $response = $anonymous->get('/projects');

        $this->assertSame(200, $response['status']);
        $body = $response['body'];

        $this->assertStringContainsString($uniqueTitle, $body);
        $this->assertStringContainsString('class="project-feed"', $body);
        $this->assertStringContainsString('<details class="project-entry">', $body);
        $this->assertStringContainsString('class="project-entry-body"', $body);
        $this->assertStringContainsString('class="project-chevron"', $body);
        $this->assertStringContainsString('PHPUnit', $body);
        $this->assertStringContainsString('cURL', $body);
        $this->assertStringContainsString('View Project', $body);

        $admin->close();
        $anonymous->close();
    }
}
