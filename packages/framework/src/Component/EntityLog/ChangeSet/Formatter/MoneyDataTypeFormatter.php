<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

use Shopsys\FrameworkBundle\Component\Money\Money;

class MoneyDataTypeFormatter
{
    /**
     * @param array{oldReadableValue: string|null, newReadableValue: string|null, oldValue: \Shopsys\FrameworkBundle\Component\Money\Money|null, newValue: \Shopsys\FrameworkBundle\Component\Money\Money|null} $changes
     * @return string
     */
    public function formatChanges(array $changes): string
    {
        $changes['oldReadableValue'] = $changes['oldReadableValue'] ? Money::create($changes['oldReadableValue'])->round(2)->getAmount() : t('empty value');
        $changes['newReadableValue'] = $changes['newReadableValue'] ? Money::create($changes['newReadableValue'])->round(2)->getAmount() : t('empty value');

        return t('from "oldReadableValue" to "newReadableValue"', $changes);
    }
}
