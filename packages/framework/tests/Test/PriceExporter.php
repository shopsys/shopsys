<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test;

use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

final class PriceExporter
{
    public function export(PriceInterface $value): string
    {
        return $value->getPriceWithVat()->getAmount() . ' with Vat (' . $value->getPriceWithoutVat()->getAmount() . ' without VAT)';
    }
}
