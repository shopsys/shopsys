<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Export;

use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockRegistry;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ExportContext;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument;

/**
 * Converts a document to the storefront-facing variant, which is never written back to the database
 */
class ContentDocumentExporter
{
    public function __construct(
        private readonly ContentBlockRegistry $contentBlockRegistry,
    ) {
    }

    /**
     * @return array{version: int, profile: string, blocks: array<int, array<string, mixed>>}
     */
    public function export(ContentDocument $document, ExportContext $context): array
    {
        return [
            'version' => $document->version,
            'profile' => $document->profile,
            'blocks' => $this->exportBlocks($document->blocks, $context),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[] $blocks
     * @return array<int, array<string, mixed>>
     */
    private function exportBlocks(array $blocks, ExportContext $context): array
    {
        $exportedBlocks = [];

        foreach ($blocks as $block) {
            // a type disabled after the content was saved is skipped
            if ($this->contentBlockRegistry->has($block->type)) {
                $exportedBlocks[] = $this->exportBlock($block, $context);
            }
        }

        return $exportedBlocks;
    }

    /**
     * @return array<string, mixed>
     */
    private function exportBlock(ContentBlockInstance $block, ExportContext $context): array
    {
        $exportedBlock = [
            'id' => $block->id,
            'type' => $block->type,
            'data' => $this->contentBlockRegistry->get($block->type)->export($block->data, $context),
        ];

        if ($block->children !== null) {
            $exportedBlock['children'] = array_map(
                fn (array $slot): array => $this->exportBlocks($slot, $context),
                $block->children,
            );
        }

        return $exportedBlock;
    }
}
