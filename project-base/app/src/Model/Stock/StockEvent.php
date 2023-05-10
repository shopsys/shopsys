<?php

declare(strict_types=1);

namespace App\Model\Stock;

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

    /**
     * @var \App\Model\Stock\Stock
     */
    protected Stock $stock;

    /**
     * @var bool
     */
    protected bool $hasChangedDomains;

    /**
     * @param \App\Model\Stock\Stock $stock
     * @param bool $hasChangedDomains
     */
    public function __construct(Stock $stock, bool $hasChangedDomains = false)
    {
        $this->stock = $stock;
        $this->hasChangedDomains = $hasChangedDomains;
    }

    /**
     * @return \App\Model\Stock\Stock
     */
    public function getStock(): Stock
    {
        return $this->stock;
    }

    /**
     * @return bool
     */
    public function hasChangedDomains(): bool
    {
        return $this->hasChangedDomains === true;
    }
}
