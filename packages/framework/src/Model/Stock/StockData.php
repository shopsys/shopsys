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
     * @var bool[]
     */
    public $isEnabledByDomain;

    /**
     * @var string|null
     */
    public $externalId;

    /**
     * @var bool|null
     */
    public $isDefault;

    /**
     * @var string|null
     */
    public $note;

    public function __construct()
    {
        $this->isEnabledByDomain = [];
        $this->isDefault = false;
    }
}
