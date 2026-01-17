<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Paginator;

interface PaginatorInterface
{
    public function getResult(int $page, ?int $pageSize): PaginationResult;

    public function getTotalCount(): int;
}
