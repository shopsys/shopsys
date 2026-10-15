<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Document;

class ContentDocument
{
    public const int CURRENT_VERSION = 1;

    /**
     * @param \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[] $blocks
     */
    public function __construct(
        public readonly int $version,
        public readonly string $profile,
        public readonly array $blocks,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[] $blocks
     */
    public function withBlocks(array $blocks): static
    {
        return new static($this->version, $this->profile, $blocks);
    }

    public function isEmpty(): bool
    {
        return $this->blocks === [];
    }

    /**
     * @return array{version: int, profile: string, blocks: array<int, array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'profile' => $this->profile,
            'blocks' => array_map(
                static fn (ContentBlockInstance $block): array => $block->toArray(),
                $this->blocks,
            ),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
