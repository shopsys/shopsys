<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

class PromoCodeLimitFactory
{
    /**
     * @param int $from
     * @param string $percent
     * @return \App\Model\Order\PromoCode\PromoCodeLimit
     */
    public function create(int $from, string $percent): PromoCodeLimit
    {
        return new PromoCodeLimit($from, $percent);
    }
}
