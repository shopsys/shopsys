<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Localization;

use IntlDateFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatPatternRepository;
use Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatter;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProvider;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
use Symfony\Component\Clock\DatePoint;
use Tests\FrameworkBundle\Test\DomainConfigHelper;

class DateTimeFormatterTest extends TestCase
{
    public static function formatDateTimeDataProvider(): array
    {
        return [
            // Have to be the same time, only formatted
            ['inputDateTime' => new DatePoint(
                '2019-08-21T06:52:47+00:00',
            ), 'dateTimeZone' => 'UTC', 'result' => "Aug 21, 2019, 6:52:47\u{202f}AM"],
            // Central Europe Time (UTC +1)
            ['inputDateTime' => new DatePoint(
                '2019-01-12T14:25:12+00:00',
            ), 'dateTimeZone' => 'Europe/Prague', 'result' => "Jan 12, 2019, 3:25:12\u{202f}PM"],
            // Central Europe Summer Time (UTC +2)
            ['inputDateTime' => new DatePoint(
                '2019-08-21T06:52:47+00:00',
            ), 'dateTimeZone' => 'Europe/Prague', 'result' => "Aug 21, 2019, 8:52:47\u{202f}AM"],
            // Mountain Standard Time (UTC -7)
            ['inputDateTime' => new DatePoint(
                '2019-08-21T06:52:47+00:00',
            ), 'dateTimeZone' => 'America/Phoenix', 'result' => "Aug 20, 2019, 11:52:47\u{202f}PM"],
        ];
    }

    #[DataProvider('formatDateTimeDataProvider')]
    public function testFormatDateTimeWithTimezone(
        DatePoint $inputDateTime,
        string $dateTimeZone,
        string $result,
    ): void {
        $mockedDomain = $this->getMockedDomain($dateTimeZone);
        $dateTimeFormatPatternRepository = new DateTimeFormatPatternRepository();
        $displayTimeZoneProvider = new DisplayTimeZoneProvider($dateTimeZone, $mockedDomain);

        $dateTimeFormatter = new DateTimeFormatter($dateTimeFormatPatternRepository, $displayTimeZoneProvider);

        $formattedDate = $dateTimeFormatter->format(
            $inputDateTime,
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::MEDIUM,
            'en',
        );

        $this->assertEquals($result, $formattedDate);
    }

    private function getMockedDomain(string $dateTimeZoneString): Domain
    {
        $settingStub = $this->createStub(Setting::class);
        $domainConfig = DomainConfigHelper::getDomainConfig(
            dateTimeZoneString: $dateTimeZoneString,
        );

        $currentAdministratorStub = $this->createStub(CurrentAdministrator::class);

        return new Domain(
            [$domainConfig],
            $settingStub,
            $currentAdministratorStub,
        );
    }
}
