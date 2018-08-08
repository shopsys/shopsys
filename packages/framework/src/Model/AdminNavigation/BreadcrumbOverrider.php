<?php

namespace Shopsys\FrameworkBundle\Model\AdminNavigation;

class BreadcrumbOverrider
{
    /**
     * @var string|null
     */
    protected $lastItemLabel;

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
