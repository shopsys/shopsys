<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Localization;

use DateTime;

interface DateTimeFormatterInterface
{
    /**
     * @param \DateTime $value
     * @param int $dateType @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @param int $timeType @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @param string $locale
     * @return string|bool
     */
    public function format(DateTime $value, $dateType, $timeType, $locale);
}
