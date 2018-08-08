<?php

namespace Shopsys\FrameworkBundle\Component\Grid;

interface GridFactoryInterface
{
    public function create(): \Shopsys\FrameworkBundle\Component\Grid\Grid;
}
