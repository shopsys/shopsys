<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Document;

class ContentBlockInstance
{
    /**
     * @param array<string, mixed> $data
     * @param \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[][]|null $children list of slots, each a list of blocks; null for non-container blocks
     */
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly array $data,
        public readonly ?array $children = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function withData(array $data): static
    {
        return new static($this->id, $this->type, $data, $this->children);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[][]|null $children
     */
    public function withChildren(?array $children): static
    {
        return new static($this->id, $this->type, $this->data, $children);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $result = [
            'id' => $this->id,
            'type' => $this->type,
            'data' => $this->data,
        ];

        if ($this->children !== null) {
            $result['children'] = array_map(
                static fn (array $slot): array => array_map(
                    static fn (ContentBlockInstance $block): array => $block->toArray(),
                    $slot,
                ),
                $this->children,
            );
        }

        return $result;
    }
}
