<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/SmokeServer.php';
require_once __DIR__ . '/Support/HttpClient.php';

/**
 * Same shared-server-per-class pattern as AuthRoutesTest (see that class's
 * docblock for why): neither test here needs a pristine database of its
 * own, so paying for a fresh `php -S` + fresh SQLite file per test method
 * would just be wasted time. setUpBeforeClass()/tearDownAfterClass() run
 * once for the whole class, via a *static* property -- that's what makes
 * this different from ResumeJourneyTest's per-method setUp()/tearDown().
 */
final class ProjectJourneyTest extends TestCase
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

    public function testProjectPageRendersCorrectly(): void
    {
        $client = new HttpClient(self::$server->baseUrl());

        $response = $client->get('/projects');

        $this->assertSame(200, $response['status']);
        $this->assertStringContainsString('<h1>Projects Catalog</h1>', $response['body']);

        $client->close();
    }

    public function testAnonymousPostToProjectCreationIsRejectedAndNoRowIsInserted(): void
    {
        $directDb = self::$server->connectDirectly();
        $countBefore = (int) $directDb->query('SELECT COUNT(*) FROM projects')->fetchColumn();

        $client = new HttpClient(self::$server->baseUrl());

        // The real create endpoint is POST /projects/create -- there is no
        // route for POST /projects at all (only GET /projects, the public
        // catalog). Posting to /projects hits the app's 404 handler, not an
        // auth check, which is why that was the wrong URL to test against.
        $response = $client->post('/projects/create', [
            'title' => 'Unauthorized Project',
            'description' => 'This should not be created.',
        ]);

        // requireAuth() in Auth.php rejects unauthenticated requests with
        // `header('Location: /login'); exit();` -- a 302 with an empty body,
        // never a 401/JSON error. Every protected route in this app behaves
        // this way (see AuthRoutesTest's dashboard/resume checks), so this
        // is the actual contract to assert against, not a guess.
        $this->assertSame(302, $response['status']);
        $this->assertSame('/login', $client->lastHeader('Location'));

        $countAfter = (int) $directDb->query('SELECT COUNT(*) FROM projects')->fetchColumn();
        $this->assertSame(
            $countBefore,
            $countAfter,
            'An anonymous POST to /projects/create must not change the number of rows in the projects table.'
        );

        $client->close();
    }
}
