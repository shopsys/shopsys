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
     * @var string|null
     */
    public $name;

    public function __construct()
    {
        $this->productQuantity = 0;
    }
}
