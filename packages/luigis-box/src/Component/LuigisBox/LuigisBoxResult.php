<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Component\LuigisBox;

class LuigisBoxResult
{
    /**
     * @param array $ids
     * @param int $itemsCount
     */
    public function __construct(
        protected readonly array $ids,
        protected readonly int $itemsCount,
    ) {
    }

    /**
     * @return array
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * @return int
     */
    public function getItemsCount(): int
    {
        return $this->itemsCount;
    }
}
