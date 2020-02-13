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
     * @param string $stockName
     * @param string $availabilityInformation
     */
    public function __construct(string $stockName, string $availabilityInformation)
    {
        $this->stockName = $stockName;
        $this->availabilityInformation = $availabilityInformation;
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
}
