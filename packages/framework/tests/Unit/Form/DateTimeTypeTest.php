<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form;

use DateTime;
use DateTimeZone;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProvider;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Form\DateTimeType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class DateTimeTypeTest extends TypeTestCase
{
    /**
     * @return array
     */
    public function getConvertDateTimeToUtcData(): array
    {
        return [
            ['input' => '15. 1. 2019 15:51:48', 'expected' => '2019-01-15 14:51:48 UTC'],
            ['input' => '15. 8. 2019 15:51:48', 'expected' => '2019-08-15 13:51:48 UTC'],
            ['input' => '15. 1. 2019 23:51:48', 'expected' => '2019-01-15 22:51:48 UTC'],
            ['input' => '15. 8. 2019 23:51:48', 'expected' => '2019-08-15 21:51:48 UTC'],
            ['input' => '15. 1. 2019 01:51:48', 'expected' => '2019-01-15 00:51:48 UTC'],
            ['input' => '15. 8. 2019 01:51:48', 'expected' => '2019-08-14 23:51:48 UTC'],
            ['input' => '15. 1. 2019 00:00:00', 'expected' => '2019-01-14 23:00:00 UTC'],
            ['input' => '15. 8. 2019 00:00:00', 'expected' => '2019-08-14 22:00:00 UTC'],
        ];
    }

    /**
     * @dataProvider getConvertDateTimeToUtcData
     * @param string $input
     * @param string $expected
     */
    public function testConvertDateTimeToUtc(string $input, string $expected): void
    {
        $form = $this->factory->create(DateTimeType::class);

        $form->submit($input);

        $this->assertEquals(new DateTime($expected), $form->getData());
    }

    /**
     * @return array
     */
    protected function getExtensions(): array
    {
        $displayTimeZone = 'Europe/Prague';
        $displayTimeZoneProvider = new DisplayTimeZoneProvider($displayTimeZone, $this->getMockedDomain($displayTimeZone));

        $dateTimeType = new DateTimeType($displayTimeZoneProvider);

        return [
            new PreloadedExtension([$dateTimeType], []),
        ];
    }

    /**
     * @param string $dateTimeZoneString
     * @return \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private function getMockedDomain(string $dateTimeZoneString): Domain
    {
        $settingMock = $this->getMockBuilder(Setting::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dateTimeZone = new DateTimeZone($dateTimeZoneString);
        $domainConfig = new DomainConfig(1, 'http://example.com', 'name', 'en', $dateTimeZone);

        return new Domain([$domainConfig], $settingMock);
    }
}
