<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

class ProductStoreAvailabilityInformation
{
    public function __construct(
        protected readonly string $storeName,
        protected readonly int $storeId,
        protected readonly string $availabilityInformation,
        protected readonly string $availabilityStatus,
    ) {
    }

    public function getStoreName(): string
    {
        return $this->storeName;
    }

    public function getStoreId(): int
    {
        return $this->storeId;
    }

    public function getAvailabilityInformation(): string
    {
        return $this->availabilityInformation;
    }

    public function getAvailabilityStatus(): string
    {
        return $this->availabilityStatus;
    }
}
