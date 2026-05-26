<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role\Section;

final readonly class RoleSection
{
    public function __construct(
        private string $identifier,
        private string $translatableName,
        private int $priority = 0,
        private ?string $icon = null,
    ) {
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getName(): string
    {
        return $this->translatableName;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }
}
