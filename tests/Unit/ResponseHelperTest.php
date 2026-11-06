<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * isAjaxRequest() in larrymayers-app/src/Core/Response.php is how every
 * controller decides "should I send back JSON or redirect the browser?" It
 * reads one header. This test class is the template for testing anything
 * that branches on superglobal state ($_SERVER, $_GET, $_POST, $_SESSION):
 *
 *   1. Set the superglobal to exactly the input you want to test.
 *   2. Call the function.
 *   3. Assert the result.
 *   4. In tearDown(), put the superglobal back how you found it -- tests
 *      must never leak state into each other. PHPUnit does not reset
 *      $_SERVER between test methods for you.
 */
final class ResponseHelperTest extends TestCase
{
    private ?string $originalHeaderValue = null;

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

    public function testReturnsTrueForTheExactHeaderBrowsersSend(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        $this->assertTrue(isAjaxRequest());
    }

    public function testIsCaseInsensitive(): void
    {
        // jQuery and most fetch() wrapper libraries send "XMLHttpRequest",
        // but the function explicitly lowercases before comparing -- this
        // test is what proves that's not accidental dead code.
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';

        $this->assertTrue(isAjaxRequest());
    }

    public function testReturnsFalseWhenHeaderIsAbsent(): void
    {
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);

        $this->assertFalse(isAjaxRequest());
    }

    public function testReturnsFalseWhenHeaderIsPresentButEmpty(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = '';

        $this->assertFalse(isAjaxRequest());
    }

    public function testReturnsFalseForAnUnrelatedValue(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'fetch';

        $this->assertFalse(isAjaxRequest());
    }
}
