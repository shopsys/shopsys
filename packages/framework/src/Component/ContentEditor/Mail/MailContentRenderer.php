<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Mail;

use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockRegistry;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument;
use Twig\Environment;

// Each block type has its own template "@ShopsysFramework/ContentEditor/Mail/<type>.html.twig", a project overrides or adds one in templates/bundles/ShopsysFrameworkBundle/ContentEditor/Mail/.
class MailContentRenderer
{
    public function __construct(
        protected readonly ContentBlockRegistry $contentBlockRegistry,
        protected readonly Environment $twig,
    ) {
    }

    /**
     * Renders the document to table-based HTML with inline styles, which mail clients can display
     */
    public function render(ContentDocument $document, int $domainId): string
    {
        return $this->renderBlocks($document->blocks, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[] $blocks
     */
    protected function renderBlocks(array $blocks, int $domainId): string
    {
        $html = '';

        foreach ($blocks as $block) {
            // a type disabled after the content was saved is skipped
            if ($this->contentBlockRegistry->has($block->type)) {
                $html .= $this->renderBlock($block, $domainId);
            }
        }

        return $html;
    }

    protected function renderBlock(ContentBlockInstance $block, int $domainId): string
    {
        return $this->twig->render('@ShopsysFramework/ContentEditor/Mail/' . $block->type . '.html.twig', [
            'data' => $this->contentBlockRegistry->get($block->type)->export($block->data, $domainId),
            'children' => array_map(
                fn (array $slot): string => $this->renderBlocks($slot, $domainId),
                $block->children ?? [],
            ),
        ]);
    }
}
