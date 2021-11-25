<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

class PromoCodeLimitFactory
{
    /**
     * @param string $from
     * @param string $discount
     * @return \App\Model\Order\PromoCode\PromoCodeLimit
     */
    public function create(string $from, string $discount): PromoCodeLimit
    {
        return new PromoCodeLimit($from, $discount);
    }
}
