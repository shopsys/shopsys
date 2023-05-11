<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Localization;

use DateTimeZone;

class DisplayTimeZoneProvider implements DisplayTimeZoneProviderInterface
{
    protected DateTimeZone $displayTimeZone;

    /**
     * @param string|null $timeZoneString
     */
    public function __construct(?string $timeZoneString = null)
    {
        if ($timeZoneString === null) {
            $timeZoneString = date_default_timezone_get();
        }

        $this->displayTimeZone = new DateTimeZone($timeZoneString);
    }

    /**
     * @return \DateTimeZone
     */
    public function getDisplayTimeZone(): DateTimeZone
    {
        return $this->displayTimeZone;
    }
}
