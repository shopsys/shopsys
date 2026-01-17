<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role\Section;

final readonly class RoleSection
{
    public function __construct(
        protected string $identifier,
        protected string $translatableName,
        protected int $priority = 0,
        protected ?string $icon = null,
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
