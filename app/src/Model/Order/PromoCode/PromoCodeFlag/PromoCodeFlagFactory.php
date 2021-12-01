<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode\PromoCodeFlag;

use App\Model\Product\Flag\Flag;

class PromoCodeFlagFactory
{
    /**
     * @param \App\Model\Product\Flag\Flag $flag
     * @param string $type
     * @return \App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag
     */
    public function create(Flag $flag, string $type): PromoCodeFlag
    {
        return new PromoCodeFlag($flag, $type);
    }
}
