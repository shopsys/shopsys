<?php

declare(strict_types=1);

namespace Tests\App\Functional\Twig;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Tests\App\Test\FunctionalTestCase;

final class CkeditorPreviewHtmlSanitizerTest extends FunctionalTestCase
{
    /**
     * @inject html_sanitizer.sanitizer.shopsys.ckeditor_preview
     */
    private HtmlSanitizerInterface $ckeditorPreviewHtmlSanitizer;

    public function testSanitizerPreservesRelativeMediaAndLinks(): void
    {
        $sanitizedContent = $this->ckeditorPreviewHtmlSanitizer->sanitize(
            '<div><p>Hello <strong>world</strong><img alt="A4tech X710BK" src="/content/wysiwyg/test-image.jpg" /></p><a href="/product/test">link</a><script>alert(1)</script></div>',
        );

        $this->assertSame(
            '<div><p>Hello <strong>world</strong><img alt="A4tech X710BK" src="/content/wysiwyg/test-image.jpg" /></p><a href="/product/test">link</a></div>',
            $sanitizedContent,
        );
    }

    public function testSanitizerNormalizesInvalidHtmlStructure(): void
    {
        $sanitizedContent = $this->ckeditorPreviewHtmlSanitizer->sanitize('<div><p>broken');

        $this->assertSame('<div><p>broken</p></div>', $sanitizedContent);
    }
}
