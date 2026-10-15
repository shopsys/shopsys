<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

use Override;
use Shopsys\FrameworkBundle\Component\Money\Money;

class MoneyDataTypeFormatter implements DataTypeFormatterInterface
{
    #[Override]
    public function supports(string $dataType): bool
    {
        return $dataType === 'Money';
    }

    #[Override]
    public function getPriority(): int
    {
        return 1;
    }

    #[Override]
    public function formatValue(mixed $readableValue, mixed $value): string
    {
        return $readableValue ? Money::create($readableValue)->round(2)->getAmount() : t('empty value');
    }
}
