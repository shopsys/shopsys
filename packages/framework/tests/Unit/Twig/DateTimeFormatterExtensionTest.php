<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Twig;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\CustomDateTimeFormatPatternRepositoryFactory;
use Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatter;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProvider;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Symfony\Component\Clock\DatePoint;
use Tests\FrameworkBundle\Test\DomainConfigHelper;

class DateTimeFormatterExtensionTest extends TestCase
{
    public static function formatDateDataProvider(): array
    {
        return [
            ['input' => new DatePoint('2015-04-08'), 'locale' => 'cs', 'result' => '8. 4. 2015'],
            ['input' => '2015-04-08', 'locale' => 'cs', 'result' => '8. 4. 2015'],

            ['input' => new DatePoint('2015-04-08'), 'locale' => 'en', 'result' => '2015-04-08'],
            ['input' => '2015-04-08', 'locale' => 'en', 'result' => '2015-04-08'],
        ];
    }

    #[DataProvider('formatDateDataProvider')]
    public function testFormatDate(mixed $input, mixed $locale, mixed $result): void
    {
        $localizationMock = $this->createLocalizationMock($locale);
        $dateTimeFormatter = $this->createDateTimeFormatter();

        $dateTimeFormatterExtension = new DateTimeFormatterExtension($dateTimeFormatter, $localizationMock);

        $this->assertSame($result, $dateTimeFormatterExtension->formatDate($input));
    }

    protected function createLocalizationMock(string $locale): Localization
    {
        $localizationMock = $this->getMockBuilder(Localization::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestLocale'])
            ->getMock();

        $localizationMock->expects($this->any())->method('getRequestLocale')
            ->willReturn($locale);

        return $localizationMock;
    }

    protected function createDateTimeFormatter(): DateTimeFormatter
    {
        $displayTimeZoneProvider = new DisplayTimeZoneProvider(DomainConfigHelper::DEFAULT_TIMEZONE_STRING, $this->getMockedDomain());
        $dateTimeFormatPatternRepository = (new CustomDateTimeFormatPatternRepositoryFactory())->create();

        return new DateTimeFormatter($dateTimeFormatPatternRepository, $displayTimeZoneProvider);
    }

    private function getMockedDomain(): Domain
    {
        $settingMock = $this->getMockBuilder(Setting::class)
            ->disableOriginalConstructor()
            ->getMock();
        $domainConfig = DomainConfigHelper::getDomainConfig();
        $currentAdministratorMock = $this->createMock(CurrentAdministrator::class);

        return new Domain(
            [$domainConfig],
            $settingMock,
            $currentAdministratorMock,
        );
    }
}
