<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

class ProductQueryParams
{
    /**
     * @var string[]|null
     */
    protected ?array $uuids = null;

    public function __construct(protected readonly int $pageSize, protected readonly int $page = 1)
    {
    }

    /**
     * @param string[] $uuids
     */
    public function withUuids(array $uuids): static
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

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }
}
