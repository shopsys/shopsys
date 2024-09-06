<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

class ScalarDataTypeFormatter
{
    /**
     * @param array{oldReadableValue: mixed, newReadableValue: mixed, oldValue: mixed, newValue: mixed} $changes
     * @return string
     */
    public function formatChanges(array $changes): string
    {
        $changes['oldReadableValue'] = $changes['oldReadableValue'] ?: t('empty value');
        $changes['newReadableValue'] = $changes['newReadableValue'] ?: t('empty value');

        return t('from "oldReadableValue" to "newReadableValue"', $changes);
    }
}
