<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

class ProductStoreAvailabilityInformation
{
    /**
     * @param string $storeName
     * @param int $storeId
     * @param string $availabilityInformation
     * @param string $availabilityStatus
     */
    public function __construct(
        protected readonly string $storeName,
        protected readonly int $storeId,
        protected readonly string $availabilityInformation,
        protected readonly string $availabilityStatus,
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
     * @return string
     */
    public function getAvailabilityStatus(): string
    {
        return $this->availabilityStatus;
    }
}
