<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block;

use Shopsys\FrameworkBundle\Component\ContentEditor\Document\Exception\ContentBlockTypeConflictException;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\Exception\UnknownContentBlockTypeException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class ContentBlockRegistry
{
    /**
     * @var array<string, \Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockInterface>|null
     */
    private ?array $blocksByType = null;

    /**
     * @param iterable<\Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockInterface> $blocks
     * @param string[] $disabledTypes
     */
    public function __construct(
        #[AutowireIterator('shopsys.content_editor.block')]
        private readonly iterable $blocks,
        private readonly array $disabledTypes,
    ) {
    }

    public function has(string $type): bool
    {
        return isset($this->getAll()[$type]);
    }

    public function get(string $type): ContentBlockInterface
    {
        return $this->getAll()[$type] ?? throw new UnknownContentBlockTypeException($type);
    }

    /**
     * @return array<string, \Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockInterface>
     */
    public function getAll(): array
    {
        $this->blocksByType ??= $this->buildBlocksByType();

        return $this->blocksByType;
    }

    /**
     * @param string[]|null $allowedTypes
     * @return array<string, \Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockInterface>
     */
    public function getForProfile(string $profile, ?array $allowedTypes = null, bool $containersAllowed = true): array
    {
        $blocks = [];

        foreach ($this->getAll() as $type => $block) {
            if (!in_array($profile, $block->getProfiles(), true)) {
                continue;
            }

            if ($allowedTypes !== null && !in_array($type, $allowedTypes, true)) {
                continue;
            }

            if (!$containersAllowed && $block instanceof ContainerContentBlockInterface) {
                continue;
            }

            $blocks[$type] = $block;
        }

        return $blocks;
    }

    /**
     * @return array<string, \Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockInterface>
     */
    private function buildBlocksByType(): array
    {
        $candidatesByType = [];

        foreach ($this->blocks as $block) {
            if (!in_array($block->getType(), $this->disabledTypes, true)) {
                $candidatesByType[$block->getType()][] = $block;
            }
        }

        $blocksByType = [];

        foreach ($candidatesByType as $type => $candidates) {
            usort($candidates, static fn (ContentBlockInterface $a, ContentBlockInterface $b): int => $b->getPriority() <=> $a->getPriority());
            $winner = $candidates[0];
            $runnerUp = $candidates[1] ?? null;

            if ($runnerUp !== null && $runnerUp->getPriority() === $winner->getPriority()) {
                throw new ContentBlockTypeConflictException((string)$type, $winner->getPriority(), $winner::class, $runnerUp::class);
            }

            $blocksByType[$type] = $winner;
        }

        return $blocksByType;
    }
}
