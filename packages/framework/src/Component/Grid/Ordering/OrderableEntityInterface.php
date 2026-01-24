<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid\Ordering;

interface OrderableEntityInterface
{
    public function setPosition(int $position): void;
}
