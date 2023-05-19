<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form;

use DateTime;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProvider;
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
        $displayTimeZoneProvider = new DisplayTimeZoneProvider('Europe/Prague');

        $dateTimeType = new DateTimeType($displayTimeZoneProvider);

        return [
            new PreloadedExtension([$dateTimeType], []),
        ];
    }
}
