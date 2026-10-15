<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Sanitizer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\ContentEditorTestFactory;

class ContentHtmlSanitizerTest extends TestCase
{
    /**
     * @return iterable<string, array{html: string, expectedResult: string}>
     */
    public static function getSanitizeData(): iterable
    {
        yield 'script is removed' => [
            'html' => '<p>Hi</p><script>alert(1)</script>',
            'expectedResult' => '<p>Hi</p>',
        ];

        yield 'event handler attribute is removed' => [
            'html' => '<p onclick="alert(1)">Hi</p>',
            'expectedResult' => '<p>Hi</p>',
        ];

        yield 'style attribute is removed' => [
            'html' => '<p style="color: red">Hi</p>',
            'expectedResult' => '<p>Hi</p>',
        ];

        yield 'javascript href is removed' => [
            'html' => '<a href="javascript:alert(1)">x</a>',
            'expectedResult' => '<a>x</a>',
        ];

        yield 'blocked wrapper element is removed but its text kept' => [
            'html' => '<p>a <font color="red">b</font></p>',
            'expectedResult' => '<p>a b</p>',
        ];

        yield 'blocked wrapper around allowed element keeps the element' => [
            'html' => '<div><p>a</p></div>',
            'expectedResult' => '<p>a</p>',
        ];

        yield 'style is removed together with its content' => [
            'html' => '<p>a</p><style>p { color: red }</style>',
            'expectedResult' => '<p>a</p>',
        ];

        yield 'rel noopener is forced for target blank' => [
            'html' => '<a href="https://example.com" target="_blank">x</a>',
            'expectedResult' => '<a href="https://example.com" target="_blank" rel="noopener">x</a>',
        ];

        yield 'rel noopener is not added without target blank' => [
            'html' => '<a href="https://example.com">x</a>',
            'expectedResult' => '<a href="https://example.com">x</a>',
        ];

        yield 'variable in text is kept' => [
            'html' => '<p>Hi {customer_name}</p>',
            'expectedResult' => '<p>Hi {customer_name}</p>',
        ];

        yield 'variable in href is kept' => [
            'html' => '<a href="{product_url}">x</a>',
            'expectedResult' => '<a href="{product_url}">x</a>',
        ];

        yield 'relative link is kept' => [
            'html' => '<a href="/contact">x</a>',
            'expectedResult' => '<a href="/contact">x</a>',
        ];

        yield 'span keeps only class' => [
            'html' => '<span class="lead" id="a">x</span>',
            'expectedResult' => '<span class="lead">x</span>',
        ];
    }

    #[DataProvider('getSanitizeData')]
    public function testSanitize(string $html, string $expectedResult): void
    {
        $sanitizer = ContentEditorTestFactory::createSanitizer();

        $this->assertSame($expectedResult, $sanitizer->sanitize($html));
    }

    public function testLongInputIsNotTruncated(): void
    {
        $sanitizer = ContentEditorTestFactory::createSanitizer();
        $html = '<p>' . str_repeat('a', 30000) . '</p>';

        $this->assertSame($html, $sanitizer->sanitize($html));
    }
}
