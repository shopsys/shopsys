<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Menu\Dropdown;

readonly class AdminDropdownMenuItem
{
    public function __construct(
        public string $title,
        public string $icon,
        public string $link,
        public int $priority,
        public bool $renderDividerBefore = false,
    ) {
    }
}
