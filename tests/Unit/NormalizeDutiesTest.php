<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * normalizeDuties() lives in larrymayers-app/src/Controllers/ResumeController.php.
 * It takes the raw text a human typed into the "Duties" textarea on the
 * resume-create/edit form (one duty per line) and turns it into a clean
 * array ready to insert as child `duties` rows.
 *
 * This is the simplest possible kind of unit test: no database, no HTTP, no
 * session. Call a pure function with a string, assert the array it returns.
 * That's exactly why it's the right place to start learning PHPUnit.
 */
final class NormalizeDutiesTest extends TestCase
{
    public function testTrimsWhitespaceFromEachLine(): void
    {
        $raw = "  Led the ETL migration  \n  Mentored two junior engineers  ";

        $result = normalizeDuties($raw);

        $this->assertSame(
            ['Led the ETL migration', 'Mentored two junior engineers'],
            $result
        );
    }

    public function testDropsBlankLinesBetweenRealLines(): void
    {
        $raw = "First duty\n\n\nSecond duty\n   \nThird duty";

        $result = normalizeDuties($raw);

        $this->assertSame(
            ['First duty', 'Second duty', 'Third duty'],
            $result
        );
    }

    public function testEmptyStringProducesAnEmptyArray(): void
    {
        $this->assertSame([], normalizeDuties(''));
    }

    public function testStringOfOnlyBlankLinesProducesAnEmptyArray(): void
    {
        $this->assertSame([], normalizeDuties("   \n\n\t\n   "));
    }

    public function testWindowsLineEndingsAreHandled(): void
    {
        // Browsers on Windows (and Windows-authored copy/paste) commonly send
        // \r\n rather than \n. If this regression ever reappears, this test
        // fails loudly instead of silently shipping a resume with one giant
        // "duty" that's actually three duties glued together.
        $raw = "Duty one\r\nDuty two\r\nDuty three";

        $this->assertSame(
            ['Duty one', 'Duty two', 'Duty three'],
            normalizeDuties($raw)
        );
    }

    public function testOldMacStyleCarriageReturnOnlyLineEndingsAreHandled(): void
    {
        $raw = "Duty one\rDuty two\rDuty three";

        $this->assertSame(
            ['Duty one', 'Duty two', 'Duty three'],
            normalizeDuties($raw)
        );
    }

    public function testResultIsReindexedFromZeroEvenWhenLeadingLinesAreBlank(): void
    {
        // array_filter() alone would leave gaps in the array keys (e.g. index
        // 0 missing if the first line was blank). normalizeDuties() must call
        // array_values() to close those gaps, because saveResumeDuties()
        // later does `foreach ($duties as $index => $duty)` and uses $index
        // directly as the database order_index -- a gap would make that
        // column skip a number for no reason.
        $raw = "\n\nReal duty";

        $result = normalizeDuties($raw);

        $this->assertSame(['Real duty'], $result);
        $this->assertArrayHasKey(0, $result);
    }
}
