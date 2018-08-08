<?php

namespace Shopsys\FrameworkBundle\Component\Grid\Ordering;

interface OrderableEntityInterface
{
    public function setPosition(int $position): void;
}
