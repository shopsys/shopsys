<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProvider;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Form\DateTimeType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Tests\FrameworkBundle\Test\DomainConfigHelper;

class DateTimeTypeTest extends TypeTestCase
{
    public static function getConvertDateTimeToUtcData(): array
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

    #[DataProvider('getConvertDateTimeToUtcData')]
    public function testConvertDateTimeToUtc(string $input, string $expected): void
    {
        $form = $this->factory->create(DateTimeType::class);

        $form->submit($input);

        $this->assertEquals(new DatePoint($expected), $form->getData());
    }

    #[Override]
    protected function getExtensions(): array
    {
        $displayTimeZone = DomainConfigHelper::DEFAULT_TIMEZONE_STRING;
        $displayTimeZoneProvider = new DisplayTimeZoneProvider($displayTimeZone, $this->getMockedDomain($displayTimeZone));

        $dateTimeType = new DateTimeType($displayTimeZoneProvider);

        return [
            new PreloadedExtension([$dateTimeType], []),
        ];
    }

    private function getMockedDomain(string $dateTimeZoneString): Domain
    {
        $settingMock = $this->getMockBuilder(Setting::class)
            ->disableOriginalConstructor()
            ->getMock();
        $domainConfig = DomainConfigHelper::getDomainConfig(
            dateTimeZoneString: $dateTimeZoneString,
        );
        $administratorFacadeMock = $this->createMock(AdministratorFacade::class);

        return new Domain(
            [$domainConfig],
            $settingMock,
            $administratorFacadeMock,
        );
    }
}
