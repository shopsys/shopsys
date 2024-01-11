<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

class ScalarDataTypeFormatter
{
    /**
     * @param array $changes
     * @return string
     */
    public function formatChanges(array $changes): string
    {
        return t('from "oldReadableValue" to "newReadableValue"', $changes);
    }
}
