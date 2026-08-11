<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Html;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Html\HtmlContentProcessor;

class HtmlContentProcessorTest extends TestCase
{
    /**
     * @return iterable<string, array{html: string|null, expectedResult: string|null}>
     */
    public static function getProcessData(): iterable
    {
        yield 'null stays null' => [
            'html' => null,
            'expectedResult' => null,
        ];

        yield 'content without links is untouched' => [
            'html' => '<p>Just a text</p>',
            'expectedResult' => '<p>Just a text</p>',
        ];

        yield 'link without target is untouched' => [
            'html' => '<a href="http://example.com">link</a>',
            'expectedResult' => '<a href="http://example.com">link</a>',
        ];

        yield 'link with target="_blank" gets rel="noopener"' => [
            'html' => '<a href="http://example.com" target="_blank">link</a>',
            'expectedResult' => '<a href="http://example.com" target="_blank" rel="noopener">link</a>',
        ];

        yield 'link with target="_blank" written without quotes gets rel="noopener"' => [
            'html' => '<a href="http://example.com" target=_blank>link</a>',
            'expectedResult' => '<a href="http://example.com" target=_blank rel="noopener">link</a>',
        ];

        yield 'the target keyword is compared case-insensitively' => [
            'html' => '<a href="http://example.com" TARGET="_BLANK">link</a>',
            'expectedResult' => '<a href="http://example.com" TARGET="_BLANK" rel="noopener">link</a>',
        ];

        yield 'whitespace around the attribute equal sign is tolerated' => [
            'html' => '<a href="http://example.com" target ="_blank">link</a>',
            'expectedResult' => '<a href="http://example.com" target ="_blank" rel="noopener">link</a>',
        ];

        yield 'the greater-than character inside an attribute value does not end the tag' => [
            'html' => '<a href="http://example.com" title="a > b" target="_blank">link</a>',
            'expectedResult' => '<a href="http://example.com" title="a > b" target="_blank" rel="noopener">link</a>',
        ];

        yield 'self-closing link keeps its closing slash' => [
            'html' => '<a href="http://example.com" target="_blank" />',
            'expectedResult' => '<a href="http://example.com" target="_blank" rel="noopener" />',
        ];

        yield 'only links with target are modified' => [
            'html' => '<a href="/first">first</a> <a href="/second" target="_blank">second</a>',
            'expectedResult' => '<a href="/first">first</a> <a href="/second" target="_blank" rel="noopener">second</a>',
        ];

        yield 'mention of target outside of a link does not modify the content' => [
            'html' => '<p>Set the target= attribute</p><a href="http://example.com">link</a>',
            'expectedResult' => '<p>Set the target= attribute</p><a href="http://example.com">link</a>',
        ];

        yield 'an element whose name starts with a is not treated as a link' => [
            'html' => '<abbr title="x" data-target="_blank">label</abbr>',
            'expectedResult' => '<abbr title="x" data-target="_blank">label</abbr>',
        ];

        yield 'content with broken encoding is processed instead of being wiped' => [
            'html' => "<a href=\"/\xC3\x28\" target=\"_blank\">link</a>",
            'expectedResult' => "<a href=\"/\xC3\x28\" target=\"_blank\" rel=\"noopener\">link</a>",
        ];
    }

    /**
     * @return iterable<string, array{html: string}>
     */
    public static function getIgnoredTargetData(): iterable
    {
        yield 'a named target' => [
            'html' => '<a href="http://example.com" target="content-frame">link</a>',
        ];

        yield 'target="_self"' => [
            'html' => '<a href="http://example.com" target="_self">link</a>',
        ];

        yield 'target="_parent"' => [
            'html' => '<a href="http://example.com" target="_parent">link</a>',
        ];

        yield 'target="_top"' => [
            'html' => '<a href="http://example.com" target="_top">link</a>',
        ];

        yield 'a value merely starting with _blank' => [
            'html' => '<a href="http://example.com" target="_blankish">link</a>',
        ];

        yield 'a target rendered from a template variable' => [
            'html' => '<a href="http://example.com" target="{{ linkTarget }}">link</a>',
        ];
    }

    /**
     * @return iterable<string, array{html: string|null, expectedResult: string|null}>
     */
    public static function getRelAttributeMergingData(): iterable
    {
        yield 'noopener is merged into a rel attribute wrapped over several lines' => [
            'html' => "<a href=\"http://example.com\" target=\"_blank\" rel=\"nofollow\n    noreferrer\">link</a>",
            'expectedResult' => '<a href="http://example.com" target="_blank" rel="nofollow noreferrer noopener">link</a>',
        ];

        yield 'noopener is merged into an existing rel attribute' => [
            'html' => '<a href="http://example.com" rel="nofollow" target="_blank">link</a>',
            'expectedResult' => '<a href="http://example.com" rel="nofollow noopener" target="_blank">link</a>',
        ];

        yield 'noopener is merged into a rel attribute written with whitespace around the equal sign' => [
            'html' => '<a href="http://example.com" target="_blank" rel = "nofollow">link</a>',
            'expectedResult' => '<a href="http://example.com" target="_blank" rel = "nofollow noopener">link</a>',
        ];

        yield 'link that already has rel="noopener" is untouched' => [
            'html' => '<a href="http://example.com" target="_blank" rel="noopener">link</a>',
            'expectedResult' => '<a href="http://example.com" target="_blank" rel="noopener">link</a>',
        ];

        yield 'noopener is not duplicated when the existing rel values are separated by a tab' => [
            'html' => "<a href=\"http://example.com\" target=\"_blank\" rel=\"nofollow\tnoopener\">link</a>",
            'expectedResult' => "<a href=\"http://example.com\" target=\"_blank\" rel=\"nofollow\tnoopener\">link</a>",
        ];

        yield 'noopener is not duplicated when the existing rel value differs in case' => [
            'html' => '<a href="http://example.com" target="_blank" rel="NOOPENER">link</a>',
            'expectedResult' => '<a href="http://example.com" target="_blank" rel="NOOPENER">link</a>',
        ];

        yield 'rel attribute written without quotes is left untouched to avoid duplicating it' => [
            'html' => '<a href="http://example.com" target="_blank" rel=nofollow>link</a>',
            'expectedResult' => '<a href="http://example.com" target="_blank" rel=nofollow>link</a>',
        ];
    }

    /**
     * @return iterable<string, array{html: string|null, expectedResult: string|null}>
     */
    public static function getContentPreservationData(): iterable
    {
        yield 'utf-8 characters and entities are preserved' => [
            'html' => '<p>Příliš žluťoučký kůň &amp; pěl</p><a href="http://example.com" target="_blank">odkaz</a>',
            'expectedResult' => '<p>Příliš žluťoučký kůň &amp; pěl</p><a href="http://example.com" target="_blank" rel="noopener">odkaz</a>',
        ];

        yield 'a style block preceding the link is kept' => [
            'html' => '<style>.a{color:red}</style><a href="http://example.com" target="_blank">link</a>',
            'expectedResult' => '<style>.a{color:red}</style><a href="http://example.com" target="_blank" rel="noopener">link</a>',
        ];

        yield 'a script block preceding the link is kept' => [
            'html' => '<script>if (a && b < c) {}</script><a href="http://example.com" target="_blank">link</a>',
            'expectedResult' => '<script>if (a && b < c) {}</script><a href="http://example.com" target="_blank" rel="noopener">link</a>',
        ];

        yield 'a whole document keeps its doctype, head and body attributes' => [
            'html' => '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body id="b">'
                . '<a href="http://example.com" target="_blank">link</a></body></html>',
            'expectedResult' => '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body id="b">'
                . '<a href="http://example.com" target="_blank" rel="noopener">link</a></body></html>',
        ];

        yield 'markup outside the edited link is not normalized' => [
            'html' => '<p><div>x</div></p><table><tr><td>y</td></tr></table>'
                . '<a href="/a?x=1&y=2&nbsp;" target="_blank">link</a>',
            'expectedResult' => '<p><div>x</div></p><table><tr><td>y</td></tr></table>'
                . '<a href="/a?x=1&y=2&nbsp;" target="_blank" rel="noopener">link</a>',
        ];

        yield 'conditional comments are kept' => [
            'html' => '<!--[if mso]><style>x</style><![endif]--><a href="http://example.com" target="_blank">link</a>',
            'expectedResult' => '<!--[if mso]><style>x</style><![endif]-->'
                . '<a href="http://example.com" target="_blank" rel="noopener">link</a>',
        ];
    }

    #[DataProvider('getProcessData')]
    public function testProcess(?string $html, ?string $expectedResult): void
    {
        $this->assertSame($expectedResult, (new HtmlContentProcessor())->process($html));
    }

    #[DataProvider('getIgnoredTargetData')]
    public function testLinkNotOpeningInNewTabIsLeftUntouched(string $html): void
    {
        $this->assertSame($html, (new HtmlContentProcessor())->process($html));
    }

    #[DataProvider('getRelAttributeMergingData')]
    public function testNoopenerIsMergedIntoTheExistingRelAttribute(?string $html, ?string $expectedResult): void
    {
        $this->assertSame($expectedResult, (new HtmlContentProcessor())->process($html));
    }

    #[DataProvider('getContentPreservationData')]
    public function testContentOutsideOfTheEditedLinkIsPreserved(?string $html, ?string $expectedResult): void
    {
        $this->assertSame($expectedResult, (new HtmlContentProcessor())->process($html));
    }
}
