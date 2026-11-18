<?php

declare(strict_types=1);

/**
 * Builds a disposable SQLite database for Playwright's web server to point
 * at (see playwright.config.js's PORTFOLIO_DB_PATH env var), so running the
 * e2e suite never touches larrymayers-app/database/app.sqlite -- the same
 * principle tests/Smoke/Support/SmokeServer.php already applies for the
 * PHPUnit layer, just run once up front here instead of per PHPUnit test
 * class, since Playwright's web server is a single long-lived process for
 * the whole spec run rather than one-php-process-per-test-class.
 *
 * Reuses SmokeServer's exact admin credentials (not duplicated ones), so
 * "logged in as the test admin" means the same thing in both test layers.
 *
 * Also seeds one resume entry (with a duty) and one project (with a
 * technology) -- a known, stable accordion entry every e2e spec can click
 * on without each spec file needing its own admin-login-and-create dance
 * first. Run manually with: php tests/e2e/bootstrap-db.php
 */

require_once __DIR__ . '/../bootstrap.php'; // defines LARRYMAYERS_APP, matching every other test file's convention
require_once __DIR__ . '/../Smoke/Support/SmokeServer.php';

// NOTE: this previously pointed at "<repo root>/database/e2e_test.sqlite",
// which doesn't exist -- the app's real database directory is
// larrymayers-app/database/ (see Database.php's own path construction),
// not a top-level database/ folder. That mismatch is exactly what the
// "unable to open database file" PDOException was: SQLite does not create
// missing parent directories, so pointing it at a directory that was never
// there failed outright.
$dbPath = LARRYMAYERS_APP . '/database/e2e_test.sqlite';

if (is_file($dbPath)) {
    unlink($dbPath);
}

$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON');

$schema = file_get_contents(LARRYMAYERS_APP . '/database/schema.sql');
$pdo->exec($schema);

$pdo->prepare('INSERT INTO users (email, password_hash) VALUES (:email, :hash)')
    ->execute([
        ':email' => SmokeServer::TEST_ADMIN_EMAIL,
        ':hash' => password_hash(SmokeServer::TEST_ADMIN_PASSWORD, PASSWORD_DEFAULT),
    ]);

$pdo->prepare('
    INSERT INTO resume (title, company, summary, start_year, end_year, order_index)
    VALUES (:title, :company, :summary, :start_year, :end_year, 1)
')->execute([
    ':title' => 'Playwright Fixture Role',
    ':company' => 'Fixture Co',
    ':summary' => 'Seeded for Playwright end-to-end tests.',
    ':start_year' => '2022',
    ':end_year' => '2023',
]);
$resumeId = (int) $pdo->lastInsertId();
$pdo->prepare('INSERT INTO duties (resume_id, duty, order_index) VALUES (:resume_id, :duty, 1)')
    ->execute([':resume_id' => $resumeId, ':duty' => 'Verified the accordion opens on click']);

$pdo->prepare('
    INSERT INTO projects (title, description, link, order_index)
    VALUES (:title, :description, :link, 1)
')->execute([
    ':title' => 'Playwright Fixture Project',
    ':description' => 'Seeded for Playwright end-to-end tests.',
    ':link' => 'https://example.test/fixture-project',
]);
$projectId = (int) $pdo->lastInsertId();
$pdo->prepare('
    INSERT INTO project_technologies (project_id, technology, order_index)
    VALUES (:project_id, :technology, 1)
')->execute([':project_id' => $projectId, ':technology' => 'Playwright']);

echo "e2e fixture database ready at {$dbPath}\n";
