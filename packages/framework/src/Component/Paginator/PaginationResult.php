<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Paginator;

class PaginationResult
{
    protected int $pageCount;

    protected int $fromItem;

    protected int $toItem;

    public function __construct(
        protected int $page,
        protected ?int $pageSize,
        protected int $totalCount,
        protected array $results,
    ) {
        if ($pageSize === 0) {
            $this->pageCount = 0;
        } elseif ($pageSize === null) {
            if ($totalCount > 0) {
                $this->pageCount = 1;
            } else {
                $this->pageCount = 0;
            }
        } else {
            $this->pageCount = (int)ceil($this->totalCount / $this->pageSize);
        }

        $this->fromItem = (($this->page - 1) * $this->pageSize) + 1;
        $this->toItem = $this->page * $this->pageSize;

        if ($this->toItem > $this->totalCount) {
            $this->toItem = $this->totalCount;
        }
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPageSize(): ?int
    {
        return $this->pageSize;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function getPageCount(): int
    {
        return $this->pageCount;
    }

    public function getFromItem(): int
    {
        return $this->fromItem;
    }

    public function getToItem(): int
    {
        return $this->toItem;
    }

    public function isFirstPage(): bool
    {
        return $this->page === 1;
    }

    public function isLastPage(): bool
    {
        return $this->page === $this->pageCount;
    }

    public function getPreviousPage(): ?int
    {
        if ($this->isFirstPage()) {
            return null;
        }

        return $this->page - 1;
    }

    public function getNextPage(): ?int
    {
        if ($this->isLastPage()) {
            return null;
        }

        return $this->page + 1;
    }
}
