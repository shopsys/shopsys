<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

use Shopsys\FrameworkBundle\Component\Money\Money;

class MoneyDataTypeFormatter extends AbstractChangeSetFormatter
{
    /**
     * @param array{oldReadableValue: string|null, newReadableValue: string|null, oldValue: \Shopsys\FrameworkBundle\Component\Money\Money|null, newValue: \Shopsys\FrameworkBundle\Component\Money\Money|null} $changes
     */
    public function formatChanges(array $changes): string
    {
        $changes['oldReadableValue'] = $this->formatCode($changes['oldReadableValue'] ? Money::create($changes['oldReadableValue'])->round(2)->getAmount() : t('empty value'));
        $changes['newReadableValue'] = $this->formatCode($changes['newReadableValue'] ? Money::create($changes['newReadableValue'])->round(2)->getAmount() : t('empty value'));

        return t('from oldReadableValue to newReadableValue', $changes);
    }
}
