<?php

declare(strict_types=1);

// Loaded once, before any test runs (see phpunit.xml's bootstrap= attribute).
// Its only job is to make the app's pure/standalone functions available to
// the Unit suite. It intentionally does NOT include Router.php, View.php, or
// Auth.php, because the Unit suite never calls view(), routes a request, or
// checks a session -- pulling those in would require faking things no unit
// test should need to know about. The Smoke suite doesn't use this file's
// requires at all; it talks to a real `php -S` process over HTTP instead.

error_reporting(E_ALL);

define('APP_ROOT', dirname(__DIR__));
define('LARRYMAYERS_APP', APP_ROOT . '/larrymayers-app');

// Same load order public/index.php uses, trimmed to what Unit tests touch.
// Database.php defines the Database class only (no connection is opened just
// by requiring the file), so it's safe to include even though no test here
// talks to a database.
require_once LARRYMAYERS_APP . '/src/Core/Database.php';
require_once LARRYMAYERS_APP . '/src/Core/Response.php';
require_once LARRYMAYERS_APP . '/src/Controllers/ResumeController.php';
require_once LARRYMAYERS_APP . '/src/Controllers/ProjectController.php';
