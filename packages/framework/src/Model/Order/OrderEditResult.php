<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

class OrderEditResult
{
    /**
     * @param bool $statusChanged
     */
    public function __construct(protected readonly bool $statusChanged)
    {
    }

    /**
     * @return bool
     */
    public function isStatusChanged()
    {
        return $this->statusChanged;
    }
}
