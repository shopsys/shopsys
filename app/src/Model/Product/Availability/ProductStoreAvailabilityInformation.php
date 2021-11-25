<?php

declare(strict_types=1);

namespace App\Model\Product\Availability;

class ProductStoreAvailabilityInformation
{
    /**
     * @var string
     */
    private string $storeName;

    /**
     * @var int
     */
    private int $storeId;

    /**
     * @var string
     */
    private string $availabilityInformation;

    /**
     * @var bool
     */
    private bool $exposedProduct;

    /**
     * @var string
     */
    private string $availabilityStatus;

    /**
     * @param string $storeName
     * @param int $storeId
     * @param string $availabilityInformation
     * @param bool $exposedProduct
     * @param string $availabilityStatus
     */
    public function __construct(string $storeName, int $storeId, string $availabilityInformation, bool $exposedProduct, string $availabilityStatus)
    {
        $this->storeName = $storeName;
        $this->storeId = $storeId;
        $this->availabilityInformation = $availabilityInformation;
        $this->exposedProduct = $exposedProduct;
        $this->availabilityStatus = $availabilityStatus;
    }

    /**
     * @return string
     */
    public function getStoreName(): string
    {
        return $this->storeName;
    }

    /**
     * @return int
     */
    public function getStoreId(): int
    {
        return $this->storeId;
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
