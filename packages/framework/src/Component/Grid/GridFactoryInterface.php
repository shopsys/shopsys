<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

interface GridFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create();
}
