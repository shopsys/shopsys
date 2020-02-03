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
     * @var string|null
     */
    public $time_valid_from;

    /**
     * @var string|null
     */
    public $time_valid_to;
}
