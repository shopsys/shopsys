<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData as BasePromoCodeData;

class PromoCodeData extends BasePromoCodeData
{
    /**
     * @var int|null
     */
    public $domainId;

    /**
     * @var \DateTime|null
     */
    public $dateValidFrom;

    /**
     * @var \DateTime|null
     */
    public $dateValidTo;

    /**
     * @var string|null
     */
    public $timeValidFrom;

    /**
     * @var string|null
     */
    public $timeValidTo;

    /**
     * @var \DateTime|null
     */
    public $datetimeValidFrom;

    /**
     * @var \DateTime|null
     */
    public $datetimeValidTo;

    /**
     * @var \App\Model\Product\Product[]
     */
    public $productsWithSale = [];

    /**
     * @var \App\Model\Category\Category[]
     */
    public $categoriesWithSale = [];
}
