<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ArrayUtils;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ArrayUtils\ArrayHelper;

class ArrayHelperTest extends TestCase
{
    /**
     * @param list<int|string> $expectedCommonKeys
     */
    #[DataProvider('getCommonKeysProvider')]
    public function testGetCommonKeys(array $array1, array $array2, array $expectedCommonKeys): void
    {
        $this->assertSame($expectedCommonKeys, ArrayHelper::getCommonKeys($array1, $array2));
    }

    /**
     * @return iterable<string, array{array, array, list<int|string>}>
     */
    public static function getCommonKeysProvider(): iterable
    {
        yield 'no common keys' => [['title' => 1], ['form' => 2], []];

        yield 'one common key' => [['title' => 1, 'gridView' => 2], ['title' => 3], ['title']];

        yield 'multiple common keys' => [
            ['title' => 1, 'form' => 2, 'gridView' => 3],
            ['form' => 4, 'title' => 5],
            ['title', 'form'],
        ];

        yield 'common key with different values' => [['title' => 1], ['title' => 2], ['title']];

        yield 'integer keys' => [[1 => 'a', 2 => 'b'], [2 => 'c', 3 => 'd'], [2]];

        yield 'empty arrays' => [[], [], []];
    }
}
