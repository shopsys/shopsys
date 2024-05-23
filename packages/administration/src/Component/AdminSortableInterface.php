<?php

declare(strict_types=1);

namespace Shopsys\Administration\Component;

interface AdminSortableInterface
{
    /**
     * @return int|null
     */
    public function getPosition(): ?int;
}
