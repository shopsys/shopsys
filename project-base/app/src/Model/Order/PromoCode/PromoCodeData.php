<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData as BasePromoCodeData;

class PromoCodeData extends BasePromoCodeData
{
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
}
