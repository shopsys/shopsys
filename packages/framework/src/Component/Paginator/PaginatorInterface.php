<?php

namespace Shopsys\FrameworkBundle\Component\Paginator;

/**
 * @template T
 */
interface PaginatorInterface
{
    /**
     * @param mixed $page
     * @param mixed $pageSize
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult<T>
     */
    public function getResult($page, $pageSize);

    public function getTotalCount();
}
