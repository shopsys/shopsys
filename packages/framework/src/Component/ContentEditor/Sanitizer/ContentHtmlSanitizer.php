<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Sanitizer;

use Shopsys\FrameworkBundle\Component\Html\HtmlContentProcessor;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

class ContentHtmlSanitizer
{
    /**
     * Element => allowed attributes; used by the bundle to configure the default sanitizer
     *
     * @var array<string, string[]>
     */
    public const array ALLOWED_ELEMENTS = [
        'p' => [],
        'br' => [],
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'u' => [],
        's' => [],
        'h2' => [],
        'h3' => [],
        'h4' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'a' => ['href', 'title', 'target', 'rel'],
        'blockquote' => [],
        'hr' => [],
        'table' => [],
        'thead' => [],
        'tbody' => [],
        'tr' => [],
        'td' => [],
        'th' => [],
        'span' => ['class'],
    ];

    /**
     * Tags are removed but their text is kept, so pasted markup does not lose content
     *
     * @var string[]
     */
    public const array BLOCKED_ELEMENTS = [
        'div', 'section', 'article', 'header', 'footer', 'main', 'aside', 'nav', 'figure', 'figcaption',
        'tfoot', 'caption', 'center', 'font', 'small', 'big',
        'pre', 'code', 'sub', 'sup', 'mark', 'cite', 'q', 'abbr', 'dl', 'dt', 'dd', 'address', 'h1', 'h5', 'h6', 'label',
    ];

    public function __construct(
        #[Autowire(service: 'html_sanitizer.sanitizer.content_editor')]
        protected readonly HtmlSanitizerInterface $htmlSanitizer,
        protected readonly HtmlContentProcessor $htmlContentProcessor,
    ) {
    }

    public function sanitize(string $html): string
    {
        // rel="noopener" depends on target="_blank", which the Symfony sanitizer cannot express
        return (string)$this->htmlContentProcessor->process($this->htmlSanitizer->sanitize($html));
    }
}
