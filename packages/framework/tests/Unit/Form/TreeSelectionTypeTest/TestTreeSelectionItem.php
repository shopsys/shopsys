<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form\TreeSelectionTypeTest;

use Override;
use Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionEntityInterface;

final class TestTreeSelectionItem implements TreeSelectionEntityInterface
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly int $level,
        private readonly bool $hasChildren,
    ) {
    }

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function getLevel(): int
    {
        return $this->level;
    }

    #[Override]
    public function hasChildren(): bool
    {
        return $this->hasChildren;
    }

    /**
     * @return array<int, self>
     */
    #[Override]
    public function getChildren(): array
    {
        return [];
    }

    #[Override]
    public function isVisible(int $domainId): bool
    {
        return true;
    }
}
