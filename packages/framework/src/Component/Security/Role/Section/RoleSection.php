<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role\Section;

final readonly class RoleSection
{
    /**
     * @param string $identifier
     * @param string $translatableName
     * @param int $priority
     * @param string|null $icon
     */
    public function __construct(
        protected string $identifier,
        protected string $translatableName,
        protected int $priority = 0,
        protected ?string $icon = null,
    ) {
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->translatableName;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }
}
