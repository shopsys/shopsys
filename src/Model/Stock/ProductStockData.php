<?php

declare(strict_types=1);

namespace App\Model\Stock;

class ProductStockData
{
    /**
     * @var int|null
     */
    public $stockId;

    /**
     * @var int|null
     */
    public $productQuantity;

    /**
     * @var bool|null
     */
    public $productExposed;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var int|null
     */
    public $futureProductQuantity;

    /**
     * @var \DateTime|null
     */
    public $dateOfStorage;

    /**
     * @var int|null
     */
    public $daysOfStorage;

    public function __construct()
    {
        $this->productQuantity = 0;
        $this->productExposed = false;
    }
}
