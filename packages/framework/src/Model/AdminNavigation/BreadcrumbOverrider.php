<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdminNavigation;

class BreadcrumbOverrider
{
    protected ?string $lastItemLabel = null;

    public function overrideLastItem(string $lastItemLabel): void
    {
        $this->lastItemLabel = $lastItemLabel;
    }

    public function getLastItemLabel(): string
    {
        return $this->lastItemLabel;
    }

    public function isLastItemOverridden(): bool
    {
        return $this->lastItemLabel !== null;
    }
}
