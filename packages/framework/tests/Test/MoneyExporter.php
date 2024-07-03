<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test;

use Shopsys\FrameworkBundle\Component\Money\Money;

final class MoneyExporter
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $value
     * @return string
     */
    public function export(Money $value): string
    {
        return $value->getAmount();
    }
}
