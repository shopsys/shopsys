<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

class ProductsResult
{
    public function __construct(protected readonly int $total, protected readonly array $hits)
    {
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getHits(): array
    {
        return $this->hits;
    }
}
