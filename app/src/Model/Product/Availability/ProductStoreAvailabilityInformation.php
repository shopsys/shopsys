<?php

declare(strict_types=1);

namespace App\Model\Product\Availability;

class ProductStoreAvailabilityInformation
{
    /**
     * @param string $storeName
     * @param int $storeId
     * @param string $availabilityInformation
     * @param bool $exposedProduct
     * @param \App\Model\Product\Availability\AvailabilityStatusEnum $availabilityStatus
     */
    public function __construct(
        private readonly string $storeName,
        private readonly int $storeId,
        private readonly string $availabilityInformation,
        private readonly bool $exposedProduct,
        private readonly AvailabilityStatusEnum $availabilityStatus
    ) {
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
     * @return \App\Model\Product\Availability\AvailabilityStatusEnum
     */
    public function getAvailabilityStatus(): AvailabilityStatusEnum
    {
        return $this->availabilityStatus;
    }
}
