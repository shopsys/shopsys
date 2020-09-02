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

    /**
     * @var int|null
     */
    public $remainingUses;

    /**
     * @var string|null
     */
    public $identifier;

    /**
     * @var bool|null
     */
    public $massGenerate;

    /**
     * @var string|null
     */
    public $prefix;

    /**
     * @var int|null
     */
    public $massGenerateBatchId;

    /**
     * @var int|null
     */
    public $quantity;

    /**
     * @var bool
     */
    public $applyOnSecondProduct = false;

    /**
     * @var bool
     */
    public $onSale = true;

    /**
     * @var bool
     */
    public $inAction = true;

    /**
     * @var bool
     */
    public $scontoPrice = true;

    /**
     * @var bool
     */
    public $withoutLowPrice = true;

    /**
     * @var bool
     */
    public $priceHit = true;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeLimit[]
     */
    public $limits = [];

    /**
     * @var int
     */
    public $discountType = PromoCode::DISCOUNT_TYPE_PERCENT;
}
