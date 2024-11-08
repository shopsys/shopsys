<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

class PriceListData
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $domainId;

    /**
     * @var \DateTimeImmutable
     */
    public $validFrom;

    /**
     * @var \DateTimeImmutable
     */
    public $validTo;
}
