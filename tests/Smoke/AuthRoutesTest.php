<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/SmokeServer.php';
require_once __DIR__ . '/Support/HttpClient.php';

/**
 * This class answers one question per test: "can an anonymous visitor do
 * something they shouldn't, or is a legitimate admin blocked from doing
 * something they should be able to?" It directly covers backlog ticket
 * PORT-20 ("add integration tests proving unauthenticated POSTs are
 * blocked"), which TESTING_CICD_STRATEGY.md calls the single highest-value
 * test for this app.
 *
 * One server is started once for the whole class (setUpBeforeClass), not
 * once per test method, because starting `php -S` and building a fresh
 * SQLite file takes real time (file I/O + a startup poll loop) and none of
 * these tests need a database their neighbors haven't touched -- each test
 * either reads state that doesn't change (the login page, the seeded admin
 * user) or proves a write did NOT happen, which doesn't depend on test
 * order. (Contrast with ResumeJourneyTest, which deliberately gets its own
 * server per test method -- see that class's docblock for why.)
 */
final class AuthRoutesTest extends TestCase
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

    public function testLoginPageRendersAFormForAnAnonymousVisitor(): void
    {
        $client = new HttpClient(self::$server->baseUrl());

        $response = $client->get('/login');

        $this->assertSame(200, $response['status']);
        $this->assertStringContainsString('name="email"', $response['body']);
        $this->assertStringContainsString('name="password"', $response['body']);

        $client->close();
    }

    public function testLoginSucceedsWithTheSeededAdminCredentials(): void
    {
        $client = new HttpClient(self::$server->baseUrl());

        $response = $client->post('/login', [
            'email' => SmokeServer::TEST_ADMIN_EMAIL,
            'password' => SmokeServer::TEST_ADMIN_PASSWORD,
        ]);

        $this->assertSame(200, $response['status']);
        $payload = json_decode($response['body'], true);
        $this->assertSame('success', $payload['status'] ?? null);
        $this->assertSame('/dashboard', $payload['redirect'] ?? null);

        $client->close();
    }

    public function testLoginIsRejectedWithTheWrongPassword(): void
    {
        $client = new HttpClient(self::$server->baseUrl());

        $response = $client->post('/login', [
            'email' => SmokeServer::TEST_ADMIN_EMAIL,
            'password' => 'definitely-the-wrong-password',
        ]);

        $this->assertSame(401, $response['status']);
        $payload = json_decode($response['body'], true);
        $this->assertSame('error', $payload['status'] ?? null);

        $client->close();
    }

    public function testAnonymousVisitorIsRedirectedAwayFromTheDashboard(): void
    {
        $client = new HttpClient(self::$server->baseUrl());

        $response = $client->get('/dashboard');

        // Auth.php's requireAuth() issues `header('Location: /login'); exit();`
        // -- a 302, not a 403/401. The test asserts the exact mechanism the
        // app actually uses, so it would fail (correctly) if a future change
        // replaced the redirect with something else that still "worked" in a
        // browser but changed the contract this test is protecting.
        $this->assertSame(302, $response['status']);
        $this->assertSame('/login', $client->lastHeader('Location'));

        $client->close();
    }

    public function testAuthenticatedAdminCanReachTheDashboard(): void
    {
        $client = new HttpClient(self::$server->baseUrl());
        $login = $client->post('/login', [
            'email' => SmokeServer::TEST_ADMIN_EMAIL,
            'password' => SmokeServer::TEST_ADMIN_PASSWORD,
        ]);
        $this->assertSame(200, $login['status']); // guard: if login itself broke, the next assertion is meaningless

        $response = $client->get('/dashboard');

        $this->assertSame(200, $response['status']);
        $this->assertStringContainsString('dashboard', $response['body']);

        $client->close();
    }

    public function testAnonymousPostToResumeCreateIsRejectedAndNoRowIsInserted(): void
    {
        $directDb = self::$server->connectDirectly();
        $countBefore = (int) $directDb->query('SELECT COUNT(*) FROM resume')->fetchColumn();

        $client = new HttpClient(self::$server->baseUrl());

        // Deliberately never logged in on this $client.
        $response = $client->post('/resume/create', [
            'title' => 'Should Never Be Saved',
            'company' => 'Nobody Inc',
            'summary' => 'This request has no session cookie at all.',
            'start_year' => '2020',
            'end_year' => '2021',
            'duties' => "Should not persist",
        ]);

        $this->assertSame(302, $response['status']);
        $this->assertSame('/login', $client->lastHeader('Location'));

        $countAfter = (int) $directDb->query('SELECT COUNT(*) FROM resume')->fetchColumn();
        $this->assertSame(
            $countBefore,
            $countAfter,
            'An anonymous POST to /resume/create must not change the number of rows in the resume table.'
        );

        $client->close();
    }
}
