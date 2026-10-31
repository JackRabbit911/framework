<?php

declare(strict_types=1);

namespace Tests\Az\Helper;

use PHPUnit\Framework\TestCase;
use Sys\Helper\Str;

final class StrTest extends TestCase
{
    private Str $str;

    protected function setUp(): void
    {
        if (!defined('APPPATH')) {
            define('APPPATH', 'application/');
        }

        $this->str = new Str();
    }

    public function testSlugSimpleRussianText(): void
    {
        $result = $this->str->slug('Привет мир');

        $this->assertIsString($result);
        $this->assertEquals('privet-mir', $result);
    }

    public function testSlugEnglishText(): void
    {
        $result = $this->str->slug('Hello World');

        $this->assertEquals('hello-world', $result);
    }

    public function testSlugWithCustomSeparator(): void
    {
        $result = $this->str->slug('Привет мир', '_');

        $this->assertEquals('privet_mir', $result);
    }

    public function testSlugLowercasesResult(): void
    {
        $result = $this->str->slug('ВЕРХНИЙ РЕГИСТР');

        $this->assertEquals('verhnij-registr', $result);
    }

    public function testSlugRemovesSpecialCharacters(): void
    {
        $result = $this->str->slug('Привет! Как @дела?');

        $this->assertEquals('privet-kak-dela', $result);
    }

    public function testSlugHandlesMultipleSpaces(): void
    {
        $result = $this->str->slug('Привет   мир');

        $this->assertEquals('privet-mir', $result);
    }

    public function testSlugEmptyString(): void
    {
        $result = $this->str->slug('');

        $this->assertEquals('', $result);
    }

    public function testSlugSingleWord(): void
    {
        $result = $this->str->slug('Тест');

        $this->assertEquals('test', $result);
    }

    public function testSlugMixedCyrillicAndLatin(): void
    {
        $result = $this->str->slug('Привет World мир');

        $this->assertEquals('privet-world-mir', $result);
    }

    // ── fileToClassName ──────────────────────────────────────────────

    public function testFileToClassNameBasic(): void
    {
        $result = $this->str->fileToClassName('application/Home/Controller/Home.php');

        $this->assertEquals('Home\Controller\Home', $result);
    }

    public function testFileToClassNameWithCustomRemoveSubstr(): void
    {
        $result = $this->str->fileToClassName('src/Service/UserService.php', 'src/');

        $this->assertEquals('Service\UserService', $result);
    }

    public function testFileToClassNameNestedPath(): void
    {
        $result = $this->str->fileToClassName('application/Api/Branch/Model/ModelBranch.php');

        $this->assertEquals('Api\Branch\Model\ModelBranch', $result);
    }

    // ── strWordCount ─────────────────────────────────────────────────

    public function testStrWordCountSimple(): void
    {
        $result = $this->str->strWordCount('один два три');

        $this->assertEquals(3, $result);
    }

    public function testStrWordCountEmpty(): void
    {
        $result = $this->str->strWordCount('');

        $this->assertEquals(0, $result);
    }

    public function testStrWordCountSingleWord(): void
    {
        $result = $this->str->strWordCount('Тест');

        $this->assertEquals(1, $result);
    }

    public function testStrWordCountRussianText(): void
    {
        $result = $this->str->strWordCount('Привет мир как дела');

        $this->assertEquals(4, $result);
    }

    public function testStrWordCountWithPunctuation(): void
    {
        $result = $this->str->strWordCount('Привет! Как дела?');

        $this->assertEquals(3, $result);
    }

    public function testStrWordCountMultipleSpaces(): void
    {
        $result = $this->str->strWordCount('один   два    три');

        $this->assertEquals(3, $result);
    }

    // ── cat ──────────────────────────────────────────────────────────

    public function testCatTruncatesString(): void
    {
        $result = $this->str->cat('один два три четыре пять', 3);

        $this->assertEquals('один два три', $result);
    }

    public function testCatDoesNotTruncateWhenWordsEqual(): void
    {
        $result = $this->str->cat('один два три', 3);

        $this->assertEquals('один два три', $result);
    }

    public function testCatDoesNotTruncateWhenWordsLess(): void
    {
        $result = $this->str->cat('один два', 5);

        $this->assertEquals('один два', $result);
    }

    public function testCatEmptyString(): void
    {
        $result = $this->str->cat('', 3);

        $this->assertEquals('', $result);
    }

    public function testCatSingleWord(): void
    {
        $result = $this->str->cat('Тест', 1);

        $this->assertEquals('Тест', $result);
    }

    public function testCatRussianText(): void
    {
        $result = $this->str->cat('Привет мир как дела', 2);

        $this->assertEquals('Привет мир', $result);
    }
}
