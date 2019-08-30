<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Localization;

use DateTime;
use IntlDateFormatter;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatPatternRepository;
use Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatter;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProvider;

class DateTimeFormatterTest extends TestCase
{
    /**
     * @return array
     */
    public function formatDateTimeDataProvider(): array
    {
        return [
            // Have to be the same time, only formatted
            ['inputDateTime' => new DateTime('2019-08-21T06:52:47+00:00'), 'displayTimeZone' => 'UTC', 'result' => 'Aug 21, 2019, 6:52:47 AM'],
            // Central Europe Time (UTC +1)
            ['inputDateTime' => new DateTime('2019-01-12T14:25:12+00:00'), 'displayTimeZone' => 'Europe/Prague', 'result' => 'Jan 12, 2019, 3:25:12 PM'],
            // Central Europe Summer Time (UTC +2)
            ['inputDateTime' => new DateTime('2019-08-21T06:52:47+00:00'), 'displayTimeZone' => 'Europe/Prague', 'result' => 'Aug 21, 2019, 8:52:47 AM'],
            // Mountain Standard Time (UTC -7)
            ['inputDateTime' => new DateTime('2019-08-21T06:52:47+00:00'), 'displayTimeZone' => 'America/Phoenix', 'result' => 'Aug 20, 2019, 11:52:47 PM'],
        ];
    }

    /**
     * @dataProvider formatDateTimeDataProvider
     * @param \DateTime $inputDateTime
     * @param string $dateTimeZone
     * @param string $result
     */
    public function testFormatDateTimeWithTimezone(DateTime $inputDateTime, string $dateTimeZone, string $result): void
    {
        $dateTimeFormatPatternRepository = new DateTimeFormatPatternRepository();
        $displayTimeZoneProvider = new DisplayTimeZoneProvider($dateTimeZone);

        $dateTimeFormatter = new DateTimeFormatter($dateTimeFormatPatternRepository, $displayTimeZoneProvider);

        $formattedDate = $dateTimeFormatter->format(
            $inputDateTime,
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::MEDIUM,
            null
        );

        $this->assertEquals($result, $formattedDate);
    }
}
