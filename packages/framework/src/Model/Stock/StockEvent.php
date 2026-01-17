<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Symfony\Contracts\EventDispatcher\Event;

class StockEvent extends Event
{
    /**
     * The CREATE event occurs once a stock was created.
     *
     * This event allows you to run jobs dependent on the stock creation.
     */
    public const CREATE = 'stock.create';
    /**
     * The UPDATE event occurs once a stock was changed.
     *
     * This event allows you to run jobs dependent on the stock change.
     */
    public const UPDATE = 'stock.update';
    /**
     * The DELETE event occurs once a stock was deleted.
     *
     * This event allows you to run jobs dependent on the stock deletion.
     */
    public const DELETE = 'stock.delete';

    public function __construct(protected Stock $stock, protected bool $hasChangedDomains = false)
    {
    }

    public function getStock(): Stock
    {
        return $this->stock;
    }

    public function hasChangedDomains(): bool
    {
        return $this->hasChangedDomains === true;
    }
}
