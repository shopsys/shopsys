<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Twig;

use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\CustomDateTimeFormatPatternRepositoryFactory;
use Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatter;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProvider;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;

class DateTimeFormatterExtensionTest extends TestCase
{
    /**
     * @return array
     */
    public function formatDateDataProvider(): array
    {
        return [
            ['input' => new DateTime('2015-04-08'), 'locale' => 'cs', 'result' => '8. 4. 2015'],
            ['input' => '2015-04-08', 'locale' => 'cs', 'result' => '8. 4. 2015'],

            ['input' => new DateTime('2015-04-08'), 'locale' => 'en', 'result' => '2015-04-08'],
            ['input' => '2015-04-08', 'locale' => 'en', 'result' => '2015-04-08'],
        ];
    }

    /**
     * @dataProvider formatDateDataProvider
     * @param mixed $input
     * @param mixed $locale
     * @param mixed $result
     */
    public function testFormatDate($input, $locale, $result): void
    {
        $localizationMock = $this->createLocalizationMock($locale);
        $dateTimeFormatter = $this->createDateTimeFormatter();

        $dateTimeFormatterExtension = new DateTimeFormatterExtension($dateTimeFormatter, $localizationMock);

        $this->assertSame($result, $dateTimeFormatterExtension->formatDate($input));
    }

    /**
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    protected function createLocalizationMock($locale): Localization
    {
        $localizationMock = $this->getMockBuilder(Localization::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLocale'])
            ->getMock();

        $localizationMock->expects($this->any())->method('getLocale')
            ->willReturn($locale);

        return $localizationMock;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatter
     */
    protected function createDateTimeFormatter(): DateTimeFormatter
    {
        $displayTimeZoneProvider = new DisplayTimeZoneProvider($this->getMockedDomain());
        $dateTimeFormatPatternRepository = (new CustomDateTimeFormatPatternRepositoryFactory())->create();

        return new DateTimeFormatter($dateTimeFormatPatternRepository, $displayTimeZoneProvider);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private function getMockedDomain(): Domain
    {
        $settingMock = $this->getMockBuilder(Setting::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dateTimeZone = new DateTimeZone('Europe/Prague');
        $domainConfig = new DomainConfig(1, 'http://example.com', 'name', 'en', $dateTimeZone);

        return new Domain([$domainConfig], $settingMock);
    }
}
