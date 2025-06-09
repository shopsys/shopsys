<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

interface GridFactoryInterface
{
    /**
     * @param string|null $editRole
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(?string $editRole): Grid;
}
