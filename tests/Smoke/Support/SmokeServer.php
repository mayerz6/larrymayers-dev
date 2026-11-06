<?php

declare(strict_types=1);

/**
 * Stages a disposable, throwaway copy of the app, serves it with PHP's own
 * built-in server, and guarantees cleanup -- the same job
 * tests/smoke/run-smoke.ps1 does in the PowerShell version, just expressed
 * as a PHP class so PHPUnit's own setUpBeforeClass()/tearDownAfterClass()
 * hooks can drive it instead of a separate runner script.
 *
 * What "disposable" means here, concretely:
 *   - A brand-new SQLite file is created fresh for every test CLASS (not
 *     every test method -- see the class docblocks in AuthRoutesTest and
 *     ResumeJourneyTest for why that granularity was chosen).
 *   - larrymayers-app/database/schema.sql is applied to it, so every table
 *     the app expects exists.
 *   - One known admin user is inserted, with a password this test file
 *     controls -- never the real production credential.
 *   - The real app.sqlite is never opened, because Database::getLiteConnection()
 *     reads a PORTFOLIO_DB_PATH environment variable (see Database.php) and
 *     this class sets that variable only for the child `php -S` process it
 *     launches, not for anything else on the machine.
 *   - start() launches `php -S 127.0.0.1:<port> -t public`, the exact same
 *     command the project's own README/handoff tells a human to run -- a
 *     smoke test is deliberately not testing some special "test mode," it's
 *     testing the real front controller.
 *   - stop() always terminates that process and deletes the temp database,
 *     even if a test threw -- call it from tearDownAfterClass().
 */
final class SmokeServer
{
    public const TEST_ADMIN_EMAIL = 'smoke-admin@example.test';
    public const TEST_ADMIN_PASSWORD = 'Sm0ke-Test-Pass!23';

    private ?string $dbPath = null;
    /** @var resource|null */
    private $process = null;
    private int $port = 0;

    public function baseUrl(): string
    {
        return "http://127.0.0.1:{$this->port}";
    }

    public function start(): void
    {
        $this->port = $this->findFreePort();
        $this->dbPath = $this->buildFixtureDatabase();

        // proc_open()'s $env must be string => string. $_SERVER (which we
        // start from, so the child process still has a normal PATH etc.)
        // contains non-scalar entries like 'argv' -- drop anything that
        // isn't a plain scalar before handing it to proc_open().
        $env = array_filter($_SERVER, static fn ($value) => is_scalar($value));
        $env['PORTFOLIO_DB_PATH'] = $this->dbPath;

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $this->process = proc_open(
            ['php', '-S', "127.0.0.1:{$this->port}", '-t', 'public'],
            $descriptors,
            $pipes,
            APP_ROOT,
            $env
        );

        if (!is_resource($this->process)) {
            throw new RuntimeException('Failed to start the disposable php -S server for smoke tests.');
        }

        // We don't read the server's stdout/stderr during the run, but the
        // pipes must not be left blocking or `php -S` can stall writing to
        // them once its internal buffer fills up.
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        fclose($pipes[0]);

        $this->waitUntilServerAcceptsConnections();
    }

    /**
     * A direct PDO connection to the same disposable SQLite file the server
     * is using, bypassing HTTP entirely. Smoke tests use this to prove a
     * write actually landed (or didn't) in the database -- a stronger check
     * than trusting the HTTP response alone, the same "unforgeable evidence"
     * idea the PowerShell tutorial gets at with its before/after reference
     * token, just done with a row count instead.
     */
    public function connectDirectly(): PDO
    {
        if ($this->dbPath === null) {
            throw new RuntimeException('Server has not been started yet.');
        }

        $pdo = new PDO('sqlite:' . $this->dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public function stop(): void
    {
        if (is_resource($this->process)) {
            proc_terminate($this->process);
            proc_close($this->process);
            $this->process = null;
        }

        if ($this->dbPath !== null && is_file($this->dbPath)) {
            unlink($this->dbPath);
        }
        $this->dbPath = null;
    }

    private function findFreePort(): int
    {
        // Binding to port 0 asks the OS to hand back any currently-free port.
        // This avoids hardcoding a port number that might already be taken by
        // another process (or another test class, if tests ever run in
        // parallel) on the developer's machine or a CI runner.
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        if ($socket === false) {
            throw new RuntimeException("Could not find a free port: {$errstr}");
        }
        $name = stream_socket_get_name($socket, false);
        fclose($socket);

        return (int) substr($name, strrpos($name, ':') + 1);
    }

    private function buildFixtureDatabase(): string
    {
        $dbPath = tempnam(sys_get_temp_dir(), 'portfolio-smoke-db-');
        // tempnam() creates an empty file; PDO needs to open a fresh SQLite
        // file at this path, so remove the empty placeholder first.
        unlink($dbPath);

        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA foreign_keys = ON');

        $schema = file_get_contents(LARRYMAYERS_APP . '/database/schema.sql');
        $pdo->exec($schema);

        $pdo->prepare('INSERT INTO users (email, password_hash) VALUES (:email, :hash)')
            ->execute([
                ':email' => self::TEST_ADMIN_EMAIL,
                ':hash' => password_hash(self::TEST_ADMIN_PASSWORD, PASSWORD_DEFAULT),
            ]);

        return $dbPath;
    }

    private function waitUntilServerAcceptsConnections(): void
    {
        $deadline = microtime(true) + 5.0;

        while (microtime(true) < $deadline) {
            $connection = @fsockopen('127.0.0.1', $this->port, $errno, $errstr, 0.25);
            if ($connection !== false) {
                fclose($connection);
                return;
            }
            usleep(100_000);
        }

        throw new RuntimeException("php -S never started listening on port {$this->port}.");
    }
}
