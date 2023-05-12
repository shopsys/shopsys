<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

class ProductQueryParams
{
    /**
     * @var string[]|null
     */
    protected ?array $uuids = null;

    /**
     * @param int $pageSize
     * @param int $page
     */
    public function __construct(protected readonly int $pageSize, protected readonly int $page = 1)
    {
    }

    /**
     * @param array $uuids
     * @return self
     */
    public function withUuids(array $uuids): self
    {
        $query = clone $this;
        $query->uuids = $uuids;

        return $query;
    }

    /**
     * @return string[]|null
     */
    public function getUuids(): ?array
    {
        return $this->uuids;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }
}
