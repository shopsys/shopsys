<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdminNavigation;

class BreadcrumbOverrider
{
    protected ?string $lastItemLabel = null;

    /**
     * @param string $lastItemLabel
     */
    public function overrideLastItem(string $lastItemLabel): void
    {
        $this->lastItemLabel = $lastItemLabel;
    }

    /**
     * @return string
     */
    public function getLastItemLabel(): string
    {
        return $this->lastItemLabel;
    }

    /**
     * @return bool
     */
    public function isLastItemOverridden(): bool
    {
        return $this->lastItemLabel !== null;
    }
}
