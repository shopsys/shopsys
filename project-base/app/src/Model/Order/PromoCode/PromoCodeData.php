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
     * @var \App\Model\Product\Brand\Brand[]
     */
    public $brandsWithSale = [];

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
     * @var \App\Model\Order\PromoCode\PromoCodeLimit[]
     */
    public $limits = [];

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag[]
     */
    public array $flags = [];

    /**
     * @var int
     */
    public $discountType = PromoCode::DISCOUNT_TYPE_PERCENT;

    /**
     * @var bool
     */
    public bool $registeredCustomerUserOnly = false;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[]
     */
    public array $limitedPricingGroups = [];
}
