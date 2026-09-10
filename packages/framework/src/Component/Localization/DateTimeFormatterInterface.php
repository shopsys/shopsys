<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Localization;

use DateTimeInterface;
use DateTimeZone;

interface DateTimeFormatterInterface
{
    /**
     * @param int $dateType @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @param int $timeType @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     */
    public function format(
        DateTimeInterface $value,
        int $dateType,
        int $timeType,
        string $locale,
        ?DateTimeZone $displayTimeZone = null,
    ): string|bool;
}
