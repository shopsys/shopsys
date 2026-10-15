<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Mail;

use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockRegistry;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ExportContext;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Twig\Environment;

// Each block type has its own template "@ShopsysFramework/ContentEditor/Mail/<type>.html.twig", a project overrides or adds one in templates/bundles/ShopsysFrameworkBundle/ContentEditor/Mail/.
class MailContentRenderer
{
    public function __construct(
        private readonly ContentBlockRegistry $contentBlockRegistry,
        private readonly Domain $domain,
        private readonly Environment $twig,
    ) {
    }

    /**
     * Renders the document to table-based HTML with inline styles, which mail clients can display
     */
    public function render(ContentDocument $document, int $domainId): string
    {
        $context = new ExportContext($domainId, $this->domain->getDomainConfigById($domainId)->getLocale());

        return $this->renderBlocks($document->blocks, $context);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[] $blocks
     */
    private function renderBlocks(array $blocks, ExportContext $context): string
    {
        $html = '';

        foreach ($blocks as $block) {
            // a type disabled after the content was saved is skipped
            if ($this->contentBlockRegistry->has($block->type)) {
                $html .= $this->renderBlock($block, $context);
            }
        }

        return $html;
    }

    private function renderBlock(ContentBlockInstance $block, ExportContext $context): string
    {
        return $this->twig->render('@ShopsysFramework/ContentEditor/Mail/' . $block->type . '.html.twig', [
            'data' => $this->contentBlockRegistry->get($block->type)->export($block->data, $context),
            'children' => array_map(
                fn (array $slot): string => $this->renderBlocks($slot, $context),
                $block->children ?? [],
            ),
        ]);
    }
}
