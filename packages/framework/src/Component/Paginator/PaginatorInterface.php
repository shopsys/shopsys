<?php

namespace Shopsys\FrameworkBundle\Component\Paginator;

interface PaginatorInterface
{
    public function getResult($page, $pageSize): void;
    public function getTotalCount(): void;
}
