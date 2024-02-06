<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

use DateTime;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;

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
     * @param array $changes
     * @return string
     */
    public function formatChanges(array $changes): string
    {
        $changes['oldReadableValue'] = $changes['oldValue'] ? $this->dateTimeFormatterExtension->formatDateTime(new DateTime($changes['oldValue'])) : t('empty value');
        $changes['newReadableValue'] = $changes['newValue'] ? $this->dateTimeFormatterExtension->formatDateTime(new DateTime($changes['newValue'])) : t('empty value');

        return t('from "oldReadableValue" to "newReadableValue"', $changes);
    }
}
