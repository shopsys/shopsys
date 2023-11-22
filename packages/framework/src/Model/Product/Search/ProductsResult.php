<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

class ProductsResult
{
    /**
     * @param int $total
     * @param mixed[] $hits
     */
    public function __construct(protected readonly int $total, protected readonly array $hits)
    {
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return mixed[]
     */
    public function getHits(): array
    {
        return $this->hits;
    }
}
