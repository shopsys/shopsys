<?php

namespace Tests\FrameworkBundle\Unit\Component\Setting;

use DateTime;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Setting\Exception\InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\Setting\SettingValue;
use stdClass;

class SettingValueTest extends TestCase
{
    /**
     * @return array<int, array<bool|null|float|int|string>>
     */
    public function editProvider(): array
    {
        return [
            ['string'],
            [0],
            [0.0],
            [false],
            [true],
            [null],
        ];
    }

    /**
     * @return array<int, mixed[]>
     */
    public function editExceptionProvider(): array
    {
        return [
            [[]],
            [new stdClass()],
        ];
    }

    /**
     * @dataProvider editProvider
     * @param string|int|float|bool|null $value
     */
    public function testEdit(string|int|float|bool|null $value): void
    {
        $settingValue = new SettingValue('name', $value, 1);
        $this->assertSame($value, $settingValue->getValue());
    }

    /**
     * @dataProvider editExceptionProvider
     * @param mixed[]|\stdClass $value
     */
    public function testEditException(array|stdClass $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SettingValue('name', $value, 1);
    }

    public function testStoreDatetime(): void
    {
        $value = new DateTime('2017-01-01 12:34:56');
        $settingValue = new SettingValue('name', $value, 1);
        $this->assertEquals($value, $settingValue->getValue());
    }
}
