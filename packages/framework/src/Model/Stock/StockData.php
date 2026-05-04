<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

class StockData
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var array<int, bool>
     */
    public $isEnabledByDomain;

    /**
     * @var string|null
     */
    public $externalId;

    /**
     * @var array<int, bool>
     */
    public $isDefaultByDomain;

    /**
     * @var string|null
     */
    public $note;

    public function __construct()
    {
        $this->isEnabledByDomain = [];
        $this->isDefaultByDomain = [];
    }
}
