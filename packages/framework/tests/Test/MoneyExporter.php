<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test;

use SebastianBergmann\Exporter\Exporter;
use Shopsys\FrameworkBundle\Component\Money\Money;

final class MoneyExporter extends Exporter
{
    /**
     * @param mixed $value
     * @param int $indentation
     * @param \SebastianBergmann\RecursionContext\Context $processed
     * @return string
     */
    protected function recursiveExport(&$value, $indentation, $processed = null): string
    {
        if ($value instanceof Money) {
            return $value->getAmount();
        }

        return parent::recursiveExport($value, $indentation, $processed);
    }
}
