<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test;

use Shopsys\FrameworkBundle\Model\Pricing\Price;

final class PriceExporter
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $value
     * @return string
     */
    public function export(Price $value): string
    {
        return $value->getPriceWithVat()->getAmount() . ' with Vat (' . $value->getPriceWithoutVat()->getAmount() . ' without VAT)';
    }
}
