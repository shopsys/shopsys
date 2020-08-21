<?php

declare(strict_types=1);

namespace App\Model\Stock\Future;

class FutureProductStockData
{
    /**
     * @var string|null
     */
    public $erpId;

    /**
     * @var string|null
     */
    public $storeCode;

    /**
     * @var string|null
     */
    public $sku;

    /**
     * @var int|null
     */
    public $amount;

    /**
     * @var \DateTime|null
     */
    public $dateExpectedArrival;

    /**
     * @var \DateTime|null
     */
    public $dateConfirmedArrival;

    /**
     * @var bool|null
     */
    public $isLate;
}
