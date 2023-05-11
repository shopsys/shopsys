<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

class ProductsResult
{
    protected int $total;

    /**
     * @var array
     */
    protected array $hits;

    /**
     * @param int $total
     * @param array $hits
     */
    public function __construct(int $total, array $hits)
    {
        $this->total = $total;
        $this->hits = $hits;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return array
     */
    public function getHits(): array
    {
        return $this->hits;
    }
}
