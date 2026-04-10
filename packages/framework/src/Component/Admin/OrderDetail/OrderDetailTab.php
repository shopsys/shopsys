<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Admin\OrderDetail;

readonly class OrderDetailTab
{
    public function __construct(
        protected string $identifier,
        protected string $translatableName,
        protected int $priority,
        protected ?string $icon,
        protected string $template,
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

    public function getTemplate(): string
    {
        return $this->template;
    }
}
