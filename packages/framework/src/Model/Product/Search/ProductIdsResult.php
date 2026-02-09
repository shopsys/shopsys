<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

class ProductIdsResult
{
    /**
     * @param int[] $ids
     */
    public function __construct(protected readonly int $total, protected readonly array $ids)
    {
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return int[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }
}
