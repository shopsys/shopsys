<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures;

use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockRegistry;
use Shopsys\FrameworkBundle\Component\ContentEditor\Sanitizer\ContentHtmlSanitizer;
use Shopsys\FrameworkBundle\Component\Html\HtmlContentProcessor;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class ContentEditorTestFactory
{
    public static function createRegistry(): ContentBlockRegistry
    {
        return new ContentBlockRegistry(
            [new DummyTextBlock(), new DummyImageBlock(), new DummyMailTextBlock(), new DummyColumnsBlock()],
            [],
        );
    }

    /**
     * Mirrors the "content_editor" sanitizer configured by ShopsysFrameworkExtension::prepend()
     */
    public static function createSanitizer(): ContentHtmlSanitizer
    {
        $config = (new HtmlSanitizerConfig())->allowRelativeLinks()->withMaxInputLength(-1);

        foreach (ContentHtmlSanitizer::ALLOWED_ELEMENTS as $element => $attributes) {
            $config = $config->allowElement($element, $attributes);
        }

        foreach (ContentHtmlSanitizer::BLOCKED_ELEMENTS as $element) {
            $config = $config->blockElement($element);
        }

        return new ContentHtmlSanitizer(new HtmlSanitizer($config), new HtmlContentProcessor());
    }
}
