<?php

namespace Shopsys\FrameworkBundle\Component\Paginator;

/**
 * @template T
 */
interface PaginatorInterface
{
    /**
     * @param int $page
     * @param int $pageSize
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult<T>
     */
    public function getResult(int $page, int $pageSize): PaginationResult;

    /**
     * @return int
     */
    public function getTotalCount(): int;
}
