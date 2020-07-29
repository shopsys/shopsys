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
     * @var int
     */
    private int $stockId;

    /**
     * @var string
     */
    private $availabilityInformation;

    /**
     * @var bool
     */
    private $exposedProduct;

    /**
     * @var string
     */
    private $availabilityStatus;

    /**
     * @param string $stockName
     * @param int $stockId
     * @param string $availabilityInformation
     * @param bool $exposedProduct
     * @param string $availabilityStatus
     */
    public function __construct(string $stockName, int $stockId, string $availabilityInformation, bool $exposedProduct, string $availabilityStatus)
    {
        $this->stockName = $stockName;
        $this->stockId = $stockId;
        $this->availabilityInformation = $availabilityInformation;
        $this->exposedProduct = $exposedProduct;
        $this->availabilityStatus = $availabilityStatus;
    }

    /**
     * @return string
     */
    public function getStockName(): string
    {
        return $this->stockName;
    }

    /**
     * @return int
     */
    public function getStockId(): int
    {
        return $this->stockId;
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

    /**
     * @return string
     */
    public function getAvailabilityStatus(): string
    {
        return $this->availabilityStatus;
    }
}
