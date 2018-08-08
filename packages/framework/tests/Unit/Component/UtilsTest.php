<?php

namespace Tests\FrameworkBundle\Unit\Component;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Utils\Utils;

class UtilsTest extends TestCase
{
    public function testIfNull(): void
    {
        $this->assertTrue(Utils::ifNull(null, true));
        $this->assertFalse(Utils::ifNull(false, true));
        $this->assertTrue(Utils::ifNull(true, false));
    }

    public function testSetArrayDefaultValueExists(): void
    {
        $array = [
            'key' => 'value',
        ];
        $expectedArray = $array;
        Utils::setArrayDefaultValue($array, 'key', 'defaultValue');

        $this->assertSame($expectedArray, $array);
    }

    public function testSetArrayDefaultValueExistsNull(): void
    {
        $array = [
            'key' => null,
        ];
        $expectedArray = $array;
        Utils::setArrayDefaultValue($array, 'key', 'defaultValue');

        $this->assertSame($expectedArray, $array);
    }

    public function testSetArrayDefaultValueNotExist(): void
    {
        $array = [
            'key' => null,
        ];
        $expectedArray = [
            'key' => null,
            0 => 'number',
        ];
        Utils::setArrayDefaultValue($array, 0, 'number');

        $this->assertSame($expectedArray, $array);
    }

    public function testMixedToArrayIfNull(): void
    {
        $this->assertSame([], Utils::mixedToArray(null));
    }

    public function testMixedToArrayIfNotArray(): void
    {
        $value = 'I am not array';
        $this->assertSame([$value], Utils::mixedToArray($value));
    }

    public function testMixedToArrayIfArray(): void
    {
        $value = ['1', 3, []];
        $this->assertSame($value, Utils::mixedToArray($value));
    }
}
