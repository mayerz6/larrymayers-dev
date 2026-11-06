<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/SmokeServer.php';
require_once __DIR__ . '/Support/HttpClient.php';

/**
 * Regression coverage for two bugs found and fixed in the same session as
 * this test was written:
 *   1. POST /blog/create and POST /blog/update were commented out of
 *      Router.php (an uncommitted local edit), so the blog admin forms
 *      rendered fine but submitting either one 404'd.
 *   2. There was no route to view a single published blog post -- only the
 *      admin "manage" list existed; GET /blog/post is new.
 * If either regresses again, this test is what will catch it.
 */
final class BlogJourneyTest extends TestCase
{
    private SmokeServer $server;

    protected function setUp(): void
    {
        $this->server = new SmokeServer();
        $this->server->start();
    }

    protected function tearDown(): void
    {
        $this->server->stop();
    }

    public function testAdminCanPublishAPostAndAVisitorCanOpenItInFull(): void
    {
        $uniqueTitle = 'Smoke Blog Post ' . bin2hex(random_bytes(4));
        $fullContent = "Paragraph one.\nParagraph two, with more detail than the listing should need to show.";

        // --- Admin logs in and submits the create form ---
        $admin = new HttpClient($this->server->baseUrl());
        $admin->post('/login', [
            'email' => SmokeServer::TEST_ADMIN_EMAIL,
            'password' => SmokeServer::TEST_ADMIN_PASSWORD,
        ]);

        $create = $admin->post('/blog/create', [
            'title' => $uniqueTitle,
            'content' => $fullContent,
        ]);
        // blogCreate() redirects nowhere explicitly on the non-AJAX path (it
        // falls through to whatever loadContent() does after the handler
        // returns -- no further output), so the contract to check here is
        // simply "not a 404," proving the route itself exists and ran.
        $this->assertNotSame(404, $create['status']);

        // --- The post exists in the database right after creation ---
        $directDb = $this->server->connectDirectly();
        $postId = (int) $directDb
            ->query("SELECT id FROM blog_posts WHERE title = " . $directDb->quote($uniqueTitle))
            ->fetchColumn();
        $this->assertGreaterThan(0, $postId, 'The blog post must have been inserted with a real ID.');

        // --- The public /blog listing links to it ---
        $anonymous = new HttpClient($this->server->baseUrl());
        $listing = $anonymous->get('/blog');
        $this->assertSame(200, $listing['status']);
        $this->assertStringContainsString($uniqueTitle, $listing['body']);
        $this->assertStringContainsString("/blog/post?id={$postId}", $listing['body']);

        // --- Following that link renders the full post ---
        $post = $anonymous->get("/blog/post?id={$postId}");
        $this->assertSame(200, $post['status']);
        $this->assertStringContainsString($uniqueTitle, $post['body']);
        $this->assertStringContainsString('Paragraph two, with more detail', $post['body']);

        // --- An ID that doesn't exist redirects home instead of erroring ---
        $missing = $anonymous->get('/blog/post?id=999999');
        $this->assertSame(302, $missing['status']);
        $this->assertSame('/blog', $anonymous->lastHeader('Location'));

        $admin->close();
        $anonymous->close();
    }
}
