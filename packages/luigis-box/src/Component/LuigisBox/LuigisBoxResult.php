<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Component\LuigisBox;

class LuigisBoxResult
{
    /**
     * @param int[] $ids
     * @param string[] $idsWithPrefix
     * @param int $itemsCount
     */
    public function __construct(
        protected readonly array $ids,
        protected readonly array $idsWithPrefix,
        protected readonly int $itemsCount,
    ) {
    }

    /**
     * @return int[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * @return string[]
     */
    public function getIdsWithPrefix(): array
    {
        return $this->idsWithPrefix;
    }

    /**
     * @return int
     */
    public function getItemsCount(): int
    {
        return $this->itemsCount;
    }
}
