<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test;

use Shopsys\FrameworkBundle\Component\Money\Money;

final class MoneyExporter
{
    public function export(Money $value): string
    {
        return $value->getAmount();
    }
}
