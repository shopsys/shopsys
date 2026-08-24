<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Symfony\Component\Clock\DatePoint;

class DateTimeDataTypeFormatter extends AbstractChangeSetFormatter
{
    public function __construct(
        protected readonly DateTimeFormatterExtension $dateTimeFormatterExtension,
    ) {
    }

    /**
     * @param array{oldReadableValue: null, newReadableValue: null, oldValue: string, newValue: string} $changes
     */
    public function formatChanges(array $changes): string
    {
        return $this->formatFromToChanges(
            $changes['oldValue'] ? $this->dateTimeFormatterExtension->formatDateTime(new DatePoint($changes['oldValue'])) : t('empty value'),
            $changes['newValue'] ? $this->dateTimeFormatterExtension->formatDateTime(new DatePoint($changes['newValue'])) : t('empty value'),
        );
    }
}
