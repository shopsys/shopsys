<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

class BooleanDataTypeFormatter extends AbstractChangeSetFormatter
{
    /**
     * @param array{oldReadableValue: bool, newReadableValue: bool, oldValue: bool, newValue: bool} $changes
     */
    public function formatChanges(array $changes): string
    {
        return $this->formatFromToChanges(
            $changes['oldValue'] ? t('Yes') : t('No'),
            $changes['newValue'] ? t('Yes') : t('No'),
        );
    }
}
