<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Document;

use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockRegistry;

class ContentDocumentTextExtractor
{
    protected const string BLOCK_LEVEL_CLOSING_TAGS_PATTERN = '~(</(?:p|h[1-6]|li|blockquote|ul|ol)>|<br\s*/?>|<hr\s*/?>)~i';

    public function __construct(
        protected readonly ContentBlockRegistry $contentBlockRegistry,
    ) {
    }

    /**
     * The document is expected to be normalized, so WYSIWYG values are already sanitized.
     */
    public function extractText(ContentDocument $document): string
    {
        $text = implode("\n", $this->extractBlockTexts($document->blocks));
        $text = $this->addLineBreaksAfterBlockTags($text);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $this->collapseWhitespace($text);
    }

    /**
     * Keeps neighbouring paragraphs from gluing together once the tags are stripped
     */
    protected function addLineBreaksAfterBlockTags(string $html): string
    {
        return (string)preg_replace(static::BLOCK_LEVEL_CLOSING_TAGS_PATTERN, "$1\n", $html);
    }

    protected function collapseWhitespace(string $text): string
    {
        $text = (string)preg_replace('~[ \t\xc2\xa0]+~', ' ', $text);
        $text = (string)preg_replace('~\s*\n\s*~', "\n", $text);

        return trim($text);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[] $blocks
     * @return string[]
     */
    protected function extractBlockTexts(array $blocks): array
    {
        $texts = [];

        foreach ($blocks as $block) {
            if (!$this->contentBlockRegistry->has($block->type)) {
                continue;
            }

            $texts[] = $this->contentBlockRegistry->get($block->type)->extractText($block->data);

            foreach ($block->children ?? [] as $slot) {
                array_push($texts, ...$this->extractBlockTexts($slot));
            }
        }

        return $texts;
    }
}
