<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Symfony\Component\Clock\DatePoint;

class DateTimeDataTypeFormatter
{
    /**
     * @param \Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension $dateTimeFormatterExtension
     */
    public function __construct(
        protected readonly DateTimeFormatterExtension $dateTimeFormatterExtension,
    ) {
    }

    /**
     * @param array{oldReadableValue: null, newReadableValue: null, oldValue: string, newValue: string} $changes
     * @return string
     */
    public function formatChanges(array $changes): string
    {
        $changes['oldReadableValue'] = $changes['oldValue'] ? $this->dateTimeFormatterExtension->formatDateTime(new DatePoint($changes['oldValue'])) : t('empty value');
        $changes['newReadableValue'] = $changes['newValue'] ? $this->dateTimeFormatterExtension->formatDateTime(new DatePoint($changes['newValue'])) : t('empty value');

        return t('from "oldReadableValue" to "newReadableValue"', $changes);
    }
}
