<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Setting;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Setting\Exception\InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\Setting\SettingValue;
use stdClass;
use Symfony\Component\Clock\DatePoint;

class SettingValueTest extends TestCase
{
    public static function editProvider(): array
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

    public static function editExceptionProvider(): array
    {
        return [
            [[]],
            [new stdClass()],
        ];
    }

    /**
     * @param mixed $value
     */
    #[DataProvider('editProvider')]
    public function testEdit($value): void
    {
        $settingValue = new SettingValue('name', $value, 1);
        $this->assertSame($value, $settingValue->getValue());
    }

    /**
     * @param mixed $value
     */
    #[DataProvider('editExceptionProvider')]
    public function testEditException($value): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SettingValue('name', $value, 1);
    }

    public function testStoreDatetime(): void
    {
        $value = new DatePoint('2017-01-01 12:34:56');
        $settingValue = new SettingValue('name', $value, 1);
        $this->assertEquals($value, $settingValue->getValue());
    }
}
