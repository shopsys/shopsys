<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Batch;

class LuigisBoxSearchBatchLoadData extends LuigisBoxBatchLoadData
{
    /**
     * @param string $type
     * @param string $endpoint
     * @param string $userIdentifier
     * @param int|null $limit
     * @param string|null $query
     * @param int|null $page
     * @param array $filter
     * @param string|null $orderingMode
     * @param string[] $facetNames
     */
    public function __construct(
        string $type,
        string $endpoint,
        string $userIdentifier,
        ?int $limit,
        protected readonly ?string $query,
        protected readonly ?int $page,
        protected readonly array $filter = [],
        protected readonly ?string $orderingMode = null,
        protected readonly array $facetNames = [],
    ) {
        parent::__construct($type, $endpoint, $userIdentifier, $limit);
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * @return string|null
     */
    public function getOrderingMode(): ?string
    {
        return $this->orderingMode;
    }

    /**
     * @return string[]
     */
    public function getFacetNames(): array
    {
        return $this->facetNames;
    }
}
