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
     * @param \App\Model\Stock\Stock $stock
     */
    public function __construct(Stock $stock)
    {
        $this->stock = $stock;
    }

    /**
     * @return \App\Model\Stock\Stock
     */
    public function getStock(): Stock
    {
        return $this->stock;
    }
}
