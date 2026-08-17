<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

class ScalarDataTypeFormatter extends AbstractChangeSetFormatter
{
    /**
     * @param array{oldReadableValue: mixed, newReadableValue: mixed, oldValue: mixed, newValue: mixed} $changes
     */
    public function formatChanges(array $changes): string
    {
        return $this->formatFromToChanges(
            $changes['oldReadableValue'] ?: t('empty value'),
            $changes['newReadableValue'] ?: t('empty value'),
        );
    }
}
