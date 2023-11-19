<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Paginator;

class PaginationResult
{
    protected int $page;

    protected ?int $pageSize;

    protected int $totalCount;

    /**
     * @var mixed[]
     */
    protected array $results;

    protected int $pageCount;

    protected int $fromItem;

    protected int $toItem;

    /**
     * @param int $page
     * @param int|null $pageSize
     * @param int $totalCount
     * @param mixed[] $results
     */
    public function __construct(int $page, ?int $pageSize, int $totalCount, array $results)
    {
        $this->page = $page;
        $this->pageSize = $pageSize;
        $this->totalCount = $totalCount;
        $this->results = $results;

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

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int|null
     */
    public function getPageSize(): ?int
    {
        return $this->pageSize;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @return mixed[]
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * @return int
     */
    public function getPageCount(): int
    {
        return $this->pageCount;
    }

    /**
     * @return int
     */
    public function getFromItem(): int
    {
        return $this->fromItem;
    }

    /**
     * @return int
     */
    public function getToItem(): int
    {
        return $this->toItem;
    }

    /**
     * @return bool
     */
    public function isFirstPage(): bool
    {
        return $this->page === 1;
    }

    /**
     * @return bool
     */
    public function isLastPage(): bool
    {
        return $this->page === $this->pageCount;
    }

    /**
     * @return int|null
     */
    public function getPreviousPage(): ?int
    {
        if ($this->isFirstPage()) {
            return null;
        }

        return $this->page - 1;
    }

    /**
     * @return int|null
     */
    public function getNextPage(): ?int
    {
        if ($this->isLastPage()) {
            return null;
        }

        return $this->page + 1;
    }
}
