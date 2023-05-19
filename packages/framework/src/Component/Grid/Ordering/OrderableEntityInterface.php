<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid\Ordering;

interface OrderableEntityInterface
{
    /**
     * @param int $position
     */
    public function setPosition($position);
}
