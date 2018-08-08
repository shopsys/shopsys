<?php

namespace Shopsys\FrameworkBundle\Component\Paginator;

class PaginationResult
{
    /**
     * @var int
     */
    private $page;

    /**
     * @var int
     */
    private $pageSize;

    /**
     * @var int
     */
    private $totalCount;

    /**
     * @var array
     */
    private $results;

    /**
     * @var int
     */
    private $pageCount;

    /**
     * @var int
     */
    private $fromItem;

    /**
     * @var int
     */
    private $toItem;

    public function __construct(int $page, int $pageSize, int $totalCount, array $results)
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

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getResults()
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
}
