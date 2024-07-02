<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Paginator;

interface PaginatorInterface
{
    /**
     * @param int $page
     * @param int|null $pageSize
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getResult(int $page, ?int $pageSize): PaginationResult;

    /**
     * @return int
     */
    public function getTotalCount(): int;
}
