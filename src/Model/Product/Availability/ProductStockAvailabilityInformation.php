<?php

declare(strict_types=1);


namespace App\Model\Product\Availability;


class ProductStockAvailabilityInformation
{
    /**
     * @var string|null
     */
    public $stockName;

    /**
     * @var string|null
     */
    public $availabilityInformation;

    public function __construct(string $stockName, string $availabilityInformation)
    {
        $this->stockName = $stockName;
        $this->availabilityInformation = $availabilityInformation;
    }

}