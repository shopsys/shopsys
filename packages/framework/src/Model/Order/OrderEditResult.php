<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

class OrderEditResult
{
    public function __construct(protected readonly bool $statusChanged)
    {
    }

    public function isStatusChanged(): bool
    {
        return $this->statusChanged;
    }
}
