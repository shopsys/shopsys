<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Batch;

class LuigisBoxBatchLoadResult
{
    /**
     * @param array<int, mixed> $data
     * @param array<int, array<string, mixed>> $facets
     */
    public function __construct(
        protected readonly array $data,
        protected readonly int $totalCount,
        protected readonly array $facets,
    ) {
    }

    /**
     * @return array<int, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFacets(): array
    {
        return $this->facets;
    }
}
