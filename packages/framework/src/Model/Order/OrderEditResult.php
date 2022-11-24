<?php

namespace Shopsys\FrameworkBundle\Model\Order;

class OrderEditResult
{
    /**
     * @var bool
     */
    protected $statusChanged;

    /**
     * @param bool $statusChanged
     */
    public function __construct(bool $statusChanged)
    {
        $this->statusChanged = $statusChanged;
    }

    /**
     * @return bool
     */
    public function isStatusChanged(): bool
    {
        return $this->statusChanged;
    }
}
