<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Document;

use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContainerContentBlockInterface;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockInterface;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockRegistry;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\MediaField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\WysiwygField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Shopsys\FrameworkBundle\Component\ContentEditor\Sanitizer\ContentHtmlSanitizer;

final class ContentDocumentNormalizer
{
    private NormalizationReport $lastReport;

    public function __construct(
        private readonly ContentBlockRegistry $contentBlockRegistry,
        private readonly ContentHtmlSanitizer $contentHtmlSanitizer,
        private readonly ContentProfileEnum $contentProfileEnum,
    ) {
        $this->lastReport = new NormalizationReport();
    }

    /**
     * @param string[]|null $allowedTypes
     */
    public function normalize(ContentDocument $document, ?array $allowedTypes = null): ContentDocument
    {
        $this->contentProfileEnum->validateCase($document->profile);
        $this->lastReport = new NormalizationReport();

        return $document->withBlocks($this->normalizeBlocks($document->blocks, $document->profile, $allowedTypes, false));
    }

    public function getLastReport(): NormalizationReport
    {
        return $this->lastReport;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[] $blocks
     * @param string[]|null $allowedTypes
     * @return \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[]
     */
    private function normalizeBlocks(array $blocks, string $profile, ?array $allowedTypes, bool $insideSlot): array
    {
        $normalizedBlocks = [];

        foreach ($blocks as $block) {
            if (!$this->isBlockAllowed($block->type, $profile, $allowedTypes)) {
                $this->lastReport->addDroppedBlockType($block->type);

                continue;
            }

            $definition = $this->contentBlockRegistry->get($block->type);

            if ($insideSlot && $definition instanceof ContainerContentBlockInterface) {
                $this->lastReport->addFlattenedContainerType($block->type);

                foreach ($block->children ?? [] as $slot) {
                    array_push($normalizedBlocks, ...$this->normalizeBlocks($slot, $profile, $allowedTypes, true));
                }

                continue;
            }

            $normalizedBlocks[] = $this->normalizeBlock($block, $definition, $profile, $allowedTypes);
        }

        return $normalizedBlocks;
    }

    /**
     * @param string[]|null $allowedTypes
     */
    private function isBlockAllowed(string $type, string $profile, ?array $allowedTypes): bool
    {
        return $this->contentBlockRegistry->has($type)
            && in_array($profile, $this->contentBlockRegistry->get($type)->getProfiles(), true)
            && ($allowedTypes === null || in_array($type, $allowedTypes, true));
    }

    /**
     * @param string[]|null $allowedTypes
     */
    private function normalizeBlock(
        ContentBlockInstance $block,
        ContentBlockInterface $definition,
        string $profile,
        ?array $allowedTypes,
    ): ContentBlockInstance {
        $data = $this->normalizeData($block->data, $definition);

        return new ContentBlockInstance(
            Uuid::isValid($block->id) ? $block->id : Uuid::uuid4()->toString(),
            $block->type,
            $data,
            $definition instanceof ContainerContentBlockInterface
                ? $this->normalizeSlots($block->children ?? [], $definition->getSlotCount($data), $profile, $allowedTypes)
                : null,
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeData(array $data, ContentBlockInterface $definition): array
    {
        $normalizedData = [];
        $hasMediaField = false;

        foreach ($definition->getFields() as $field) {
            $value = $field->normalizeValue($data[$field->getName()] ?? $field->getDefault());

            if ($field instanceof WysiwygField && is_string($value)) {
                $value = $this->contentHtmlSanitizer->sanitize($value);
            }

            $normalizedData[$field->getName()] = $value;
            $hasMediaField = $hasMediaField || $field instanceof MediaField;
        }

        // images created before the media library are referenced by url only and have no media id
        if ($hasMediaField && is_string($data['url'] ?? null) && $data['url'] !== '') {
            $normalizedData['url'] = $data['url'];
        }

        return $normalizedData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[][] $slots
     * @param string[]|null $allowedTypes
     * @return \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[][]
     */
    private function normalizeSlots(array $slots, int $slotCount, string $profile, ?array $allowedTypes): array
    {
        $normalizedSlots = array_map(
            fn (array $slot): array => $this->normalizeBlocks($slot, $profile, $allowedTypes, true),
            array_values($slots),
        );

        return SlotFitter::fit($normalizedSlots, $slotCount);
    }
}
