<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

interface GridFactoryInterface
{
    public function create(?string $roleConstant): Grid;
}
