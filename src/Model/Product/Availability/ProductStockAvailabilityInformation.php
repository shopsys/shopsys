<?php

declare(strict_types=1);

namespace App\Model\Product\Availability;

class ProductStockAvailabilityInformation
{
    /**
     * @var string
     */
    private $stockName;

    /**
     * @var string
     */
    private $availabilityInformation;

    /**
     * @var bool
     */
    private $exposedProduct;

    /**
     * @param string $stockName
     * @param string $availabilityInformation
     * @param bool $exposedProduct
     */
    public function __construct(string $stockName, string $availabilityInformation, bool $exposedProduct)
    {
        $this->stockName = $stockName;
        $this->availabilityInformation = $availabilityInformation;
        $this->exposedProduct = $exposedProduct;
    }

    /**
     * @return string
     */
    public function getStockName(): string
    {
        return $this->stockName;
    }

    /**
     * @return string
     */
    public function getAvailabilityInformation(): string
    {
        return $this->availabilityInformation;
    }

    /**
     * @return bool
     */
    public function isExposedProduct(): bool
    {
        return $this->exposedProduct;
    }
}
