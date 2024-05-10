<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test;

use SebastianBergmann\Exporter\Exporter;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

final class PriceExporter extends Exporter
{
    /**
     * @param mixed $value
     * @param int $indentation
     * @param \SebastianBergmann\RecursionContext\Context $processed
     * @return string
     */
    protected function recursiveExport(&$value, $indentation, $processed = null): string
    {
        if ($value instanceof Price) {
            return $value->getPriceWithVat()->getAmount() . ' with Vat (' . $value->getPriceWithoutVat()->getAmount() . ' without VAT)';
        }

        return parent::recursiveExport($value, $indentation, $processed);
    }
}
