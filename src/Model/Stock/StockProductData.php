<?php

declare(strict_types=1);


namespace App\Model\Stock;


class StockProductData
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

    /**
     * @var \App\Model\Stock\Stock|null
     */
    public $stock;

    public function __construct()
    {
        $this->productQuantity = 0;
    }

}