<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

class PromoCodeData
{
    /**
     * @var string|null
     */
    public $code;

    /**
     * @var string
     */
    public $discountType = PromoCodeTypeEnum::PERCENT;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit[]
     */
    public $limits = [];

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
     * @var \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public $productsWithSale = [];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public $categoriesWithSale = [];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public $brandsWithSale = [];

    /**
     * @var int|null
     */
    public $remainingUses;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag[]
     */
    public $flags = [];

    /**
     * @var bool
     */
    public $registeredCustomerUserOnly = false;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[]
     */
    public $limitedPricingGroups = [];

    /**
     * @var bool|null
     */
    public $massGenerate = false;

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

    public function __clone()
    {
        foreach ($this->flags as $key => $flag) {
            $this->flags[$key] = clone $flag;
        }

        foreach ($this->limits as $key => $limit) {
            $this->limits[$key] = clone $limit;
        }
    }
}
