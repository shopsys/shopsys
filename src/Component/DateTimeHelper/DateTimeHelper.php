<?php

declare(strict_types=1);

namespace App\Component\DateTimeHelper;

use DateTime;
use DateTimeZone;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;

class DateTimeHelper
{
    public const UTC_TIMEZONE = 'UTC';
    public const TIME_REGEX = '#^([01]?[0-9]|2[0-3]):[0-5][0-9]$#'; //hh:mm

    /**
     * @var \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface
     */
    private $displayTimeZoneProvider;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface $displayTimeZoneProvider
     */
    public function __construct(DisplayTimeZoneProviderInterface $displayTimeZoneProvider)
    {
        $this->displayTimeZoneProvider = $displayTimeZoneProvider;
    }

    /**
     * @param string $original
     * @return \DateTime
     */
    public function convertDatetimeStringFromDisplayTimeZoneToUtc(string $original): DateTime
    {
        $dateTime = new DateTime($original, $this->displayTimeZoneProvider->getDisplayTimeZone());
        $dateTime->setTimezone(new DateTimeZone(self::UTC_TIMEZONE));

        return $dateTime;
    }

    /**
     * @param \DateTime $dateTime
     * @return \DateTime
     */
    public function convertDateTimeFromUtcToDisplayTimeZone(DateTime $dateTime): DateTime
    {
        $dateTime->setTimezone($this->displayTimeZoneProvider->getDisplayTimeZone());

        return $dateTime;
    }
}
