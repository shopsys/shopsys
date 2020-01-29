<?php

declare(strict_types=1);

namespace App\Model\Stock;

class StockSettingsData
{
    public const DELIVERY_DAYS_ON_STOCK = 'deliveryDayOnStock';
    public const TRANSFER_DAYS_BETWEEN_STOCKS = 'transferDaysBetweenStocks';

    /**
     * @var int|null
     */
    public $delivery;

    /**
     * @var int|null
     */
    public $transfer;
}
