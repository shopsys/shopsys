<?php

declare(strict_types=1);

namespace App\Model\Stock;

class StockData
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var int|null
     */
    public $domainId;

    /**
     * @var bool|null
     */
    public $centralStock;

    /**
     * @var string|null
     */
    public $externalId;

    /**
     * @var string|null
     */
    public $street;

    /**
     * @var string|null
     */
    public $city;

    /**
     * @var string|null
     */
    public $openingHours;

    public function __construct()
    {
        $this->centralStock = false;
    }
}
