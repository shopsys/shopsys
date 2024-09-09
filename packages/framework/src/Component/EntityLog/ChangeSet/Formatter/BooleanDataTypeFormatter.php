<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

class BooleanDataTypeFormatter
{
    /**
     * @param array{oldReadableValue: bool, newReadableValue: bool, oldValue: bool, newValue: bool} $changes
     * @return string
     */
    public function formatChanges(array $changes): string
    {
        $changes['oldReadableValue'] = $changes['oldValue'] ? t('Yes') : t('No');
        $changes['newReadableValue'] = $changes['newValue'] ? t('Yes') : t('No');

        return t('from "oldReadableValue" to "newReadableValue"', $changes);
    }
}
