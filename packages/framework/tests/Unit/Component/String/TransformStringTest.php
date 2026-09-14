<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\String;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;

class TransformStringTest extends TestCase
{
    private TransformStringHelper $transformStringHelper;

    #[Override]
    protected function setUp(): void
    {
        $this->transformStringHelper = new TransformStringHelper();

        parent::setUp();
    }

    public static function safeFilenameProvider(): array
    {
        return [
            [
                'actual' => 'ěščřžýáíé.dat',
                'expected' => 'escrzyaie.dat',
            ],
            [
                'actual' => 'ĚŠČŘŽÝÁÍÉ.DAT',
                'expected' => 'ESCRZYAIE.DAT',
            ],
            [
                'actual' => 'Foo     Bar.dat',
                'expected' => 'Foo_Bar.dat',
            ],
            [
                'actual' => 'Foo-Bar.dat',
                'expected' => 'Foo-Bar.dat',
            ],
            [
                'actual' => '../../Foo.dat',
                'expected' => '_._Foo.dat',
            ],
            [
                'actual' => '..\\..\\Foo.dat',
                'expected' => '_._Foo.dat',
            ],
            [
                'actual' => '.foo.dat',
                'expected' => 'foo.dat',
            ],
            [
                'actual' => 'BG 747 fixˇ.dat',
                'expected' => 'BG_747_fix.dat',
            ],
        ];
    }

    #[DataProvider('safeFilenameProvider')]
    public function testSafeFilename(mixed $actual, mixed $expected): void
    {
        $this->assertSame($expected, $this->transformStringHelper->safeFilename($actual));
    }

    public static function stringToFriendlyUrlSlugProvider(): array
    {
        return [
            [
                'actual' => 'ěščřžýáíé foo',
                'expected' => 'escrzyaie-foo',
            ],
            [
                'actual' => 'ĚŠČŘŽÝÁÍÉ   ',
                'expected' => 'escrzyaie',
            ],
            [
                'actual' => 'Foo     Bar-Baz',
                'expected' => 'foo-bar-baz',
            ],
            [
                'actual' => 'foo-bar_baz',
                'expected' => 'foo-bar_baz',
            ],
            [
                'actual' => '$€@!?<>=;~%^&',
                'expected' => '',
            ],
            [
                'actual' => 'Příliš žluťoučký kůň úpěl ďábelské ódy',
                'expected' => 'prilis-zlutoucky-kun-upel-dabelske-ody',
            ],
            [
                'actual' => 'BG-747 is fixedˇ',
                'expected' => 'bg-747-is-fixed',
            ],
        ];
    }

    #[DataProvider('stringToFriendlyUrlSlugProvider')]
    public function testStringToFriendlyUrlSlug(mixed $actual, mixed $expected): void
    {
        $this->assertSame($expected, $this->transformStringHelper->stringToFriendlyUrlSlug($actual));
    }

    public static function stringToCamelCaseProvider(): array
    {
        return [
            [
                'actual' => 'ěščřžýáíé foo',
                'expected' => 'escrzyaieFoo',
            ],
            [
                'actual' => 'ĚŠČŘŽÝÁÍÉ   ',
                'expected' => 'escrzyaie',
            ],
            [
                'actual' => 'Foo     Bar-Baz',
                'expected' => 'fooBarBaz',
            ],
            [
                'actual' => 'foo-bar_baz',
                'expected' => 'fooBarBaz',
            ],
            [
                'actual' => '$€@!?<>=;~%^&',
                'expected' => '',
            ],
            [
                'actual' => 'Příliš žluťoučký kůň úpěl ďábelské ódy',
                'expected' => 'prilisZlutouckyKunUpelDabelskeOdy',
            ],
            [
                'actual' => 'BG-747 is fixedˇ',
                'expected' => 'bg747IsFixed',
            ],
            [
                'actual' => 'camelCase-camelCase',
                'expected' => 'camelCaseCamelCase',
            ],
            [
                'actual' => 'camelCaseACRONYM ACRONYM',
                'expected' => 'camelCaseAcronymAcronym',
            ],
        ];
    }

    #[DataProvider('stringToCamelCaseProvider')]
    public function testStringToCamelCase(mixed $actual, mixed $expected): void
    {
        $this->assertSame($expected, $this->transformStringHelper->stringToCamelCase($actual));
    }

    public static function stringTrailingSlashesProvider(): array
    {
        return [
            [
                'foo',
                'foo/',
            ],
            [
                'foo/bar',
                'foo/bar/',
            ],
            [
                'foo/',
                'foo',
            ],
            [
                'foo/bar/',
                'foo/bar',
            ],
            [
                '',
                '/',
            ],
            [
                '/',
                '',
            ],
        ];
    }

    #[DataProvider('stringTrailingSlashesProvider')]
    public function testAddOrRemoveTrailingSlashFromString(string $string, string $expected): void
    {
        static::assertSame($expected, $this->transformStringHelper->addOrRemoveTrailingSlashFromString($string));
    }

    #[DataProvider('trimmedStringOrNullProvider')]
    public function testGetTrimmedStringOrNullOnEmpty(?string $original, ?string $expected): void
    {
        static::assertSame($expected, $this->transformStringHelper->getTrimmedStringOrNullOnEmpty($original));
    }

    public static function trimmedStringOrNullProvider(): array
    {
        return [
            [
                'foo ',
                'foo',
            ],
            [
                'foo  ',
                'foo',
            ],
            [
                "\t  foo",
                'foo',
            ],
            [
                "foo\n\t",
                'foo',
            ],
            [
                '',
                null,
            ],
            [
                '  ',
                null,
            ],
        ];
    }

    #[DataProvider('truncateToWholeWordsDataProvider')]
    public function testTruncateToWholeWords(string $text, int $maxLength, string $expected): void
    {
        $this->assertSame($expected, TransformStringHelper::truncateToWholeWords($text, $maxLength));
    }

    public static function truncateToWholeWordsDataProvider(): iterable
    {
        yield 'text within the limit is untouched' => [
            'text' => 'short text',
            'maxLength' => 20,
            'expected' => 'short text',
        ];

        yield 'text of exactly the limit is untouched' => [
            'text' => 'ten chars!',
            'maxLength' => 10,
            'expected' => 'ten chars!',
        ];

        yield 'cut happens at the last whitespace within the limit' => [
            'text' => 'lorem ipsum dolor sit amet',
            'maxLength' => 14,
            'expected' => 'lorem ipsum',
        ];

        yield 'word ending exactly at the limit is kept whole' => [
            'text' => 'lorem ipsum dolor sit amet',
            'maxLength' => 11,
            'expected' => 'lorem ipsum',
        ];

        yield 'single word longer than the limit is cut hard' => [
            'text' => 'supercalifragilisticexpialidocious',
            'maxLength' => 10,
            'expected' => 'supercalif',
        ];

        yield 'multibyte characters are counted as single characters' => [
            'text' => 'příliš žluťoučký kůň úpěl ďábelské ódy',
            'maxLength' => 22,
            'expected' => 'příliš žluťoučký kůň',
        ];

        yield 'trailing punctuation left after the cut is removed' => [
            'text' => 'first sentence, second sentence',
            'maxLength' => 17,
            'expected' => 'first sentence',
        ];
    }

    #[DataProvider('convertHtmlToPlainTextDataProvider')]
    public function testConvertHtmlToPlainText(?string $htmlString, ?string $expected): void
    {
        $this->assertSame($expected, TransformStringHelper::convertHtmlToPlainText($htmlString));
    }

    public static function convertHtmlToPlainTextDataProvider(): iterable
    {
        yield 'null' => [
            'htmlString' => null,
            'expected' => null,
        ];

        yield 'empty string' => [
            'htmlString' => '',
            'expected' => '',
        ];

        yield 'string without html tags' => [
            'htmlString' => 'foo bar',
            'expected' => 'foo bar',
        ];

        yield 'string with html tags' => [
            'htmlString' => '<p>foo <strong>bar</strong></p>',
            'expected' => 'foo bar',
        ];

        yield 'string with html tags and new line' => [
            'htmlString' => "<p>foo\n<strong>bar</strong></p>",
            'expected' => 'foo bar',
        ];

        yield 'string with html tags and new line and trailing space' => [
            'htmlString' => "<p>foo\n<strong>bar</strong></p> ",
            'expected' => 'foo bar',
        ];

        yield 'string with html tags and new line and space and tab' => [
            'htmlString' => "<p>foo\n<strong>bar</strong></p> \t",
            'expected' => 'foo bar',
        ];

        yield 'string with html tags and new line and tab and multiple spaces' => [
            'htmlString' => "<p>foo\n<strong>bar</strong></p> \t  ",
            'expected' => 'foo bar',
        ];

        yield 'string with html tags and new lines and spaces and tab' => [
            'htmlString' => "<p>foo\n<strong>bar</strong></p> \t  \n",
            'expected' => 'foo bar',
        ];

        yield 'string with html tags and new lines and spaces and tab and trailing space' => [
            'htmlString' => "<p>foo\n<strong>bar</strong></p> \t  \n ",
            'expected' => 'foo bar',
        ];

        yield 'string with html tags and new lines and spaces and tabs' => [
            'htmlString' => "<p>foo\n<strong>bar</strong></p> \t  \n \t",
            'expected' => 'foo bar',
        ];

        yield 'string with html entities' => [
            'htmlString' => '&#34;foo &amp; bar&#34;',
            'expected' => '"foo & bar"',
        ];
    }
}
