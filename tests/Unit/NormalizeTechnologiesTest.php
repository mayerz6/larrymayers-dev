<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * normalizeTechnologies() in ProjectController.php is byte-for-byte the same
 * logic as normalizeDuties() (same regex, same array_filter/array_values
 * pipeline), just applied to the "Technologies" textarea on the project
 * create/edit form instead of "Duties". It gets its own test class rather
 * than being merged into NormalizeDutiesTest for two reasons:
 *
 *   1. It's a genuinely different function with its own name and call
 *      site -- if someone changes one and not the other, these two test
 *      files are what notice the drift.
 *   2. One assertion here (the mixed-language entry) is specific to how
 *      technology tags are actually used in this app, not duties.
 */
final class NormalizeTechnologiesTest extends TestCase
{
    public function testSplitsAndTrimsOneTechnologyPerLine(): void
    {
        $raw = "PHP\n  PDO/SQLite  \nVanilla JavaScript";

        $this->assertSame(
            ['PHP', 'PDO/SQLite', 'Vanilla JavaScript'],
            normalizeTechnologies($raw)
        );
    }

    public function testBlankLinesBetweenEntriesAreDropped(): void
    {
        $raw = "AWS Glue\n\nAmazon Redshift\n\n\nPython";

        $this->assertSame(
            ['AWS Glue', 'Amazon Redshift', 'Python'],
            normalizeTechnologies($raw)
        );
    }

    public function testEmptyStringProducesAnEmptyArray(): void
    {
        $this->assertSame([], normalizeTechnologies(''));
    }

    public function testSingleEntryWithNoLineBreaksStillWorks(): void
    {
        $this->assertSame(['SQLite'], normalizeTechnologies('SQLite'));
    }

    public function testEntryContainingPunctuationIsPreservedVerbatim(): void
    {
        // Real data from Career_Projects.md looks like this -- make sure the
        // trim/split logic never mangles punctuation inside a single line.
        $raw = "Node.js\nVB.NET\nC#";

        $this->assertSame(['Node.js', 'VB.NET', 'C#'], normalizeTechnologies($raw));
    }
}
