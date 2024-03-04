<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Batch;

class LuigisBoxBatchLoadData
{
    /**
     * @param string $type
     * @param int $limit
     * @param string|null $query
     * @param string|null $endpoint
     * @param int|null $page
     * @param array|null $filter
     * @param string|null $orderingMode
     */
    public function __construct(
        protected readonly string $type,
        protected readonly int $limit,
        protected readonly ?string $query = null,
        protected readonly ?string $endpoint = null,
        protected readonly ?int $page = null,
        protected readonly ?array $filter = null,
        protected readonly ?string $orderingMode = null,
    ) {
    }

    /**
     * @return string|null
     */
    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    /**
     * @return int|null
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return array|null
     */
    public function getFilter(): ?array
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
}
