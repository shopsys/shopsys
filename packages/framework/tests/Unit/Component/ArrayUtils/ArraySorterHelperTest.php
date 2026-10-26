<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ArrayUtils;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ArrayUtils\ArraySorterHelper;

class ArraySorterHelperTest extends TestCase
{
    public static function sortArrayAlphabeticallyByValueProvider(): iterable
    {
        yield 'reversed order' => [
            'locale' => 'en',
            'values' => ['c', 'b', 'a'],
            'expectedValues' => ['a', 'b', 'c'],
        ];

        yield 'already sorted' => [
            'locale' => 'en',
            'values' => ['a', 'b', 'c'],
            'expectedValues' => ['a', 'b', 'c'],
        ];

        yield 'czech collation' => [
            'locale' => 'cs',
            'values' => ['i', 'ch', 'černá', 'h', 'cena'],
            'expectedValues' => ['cena', 'černá', 'h', 'ch', 'i'],
        ];
    }

    /**
     * @param string[] $values
     * @param string[] $expectedValues
     */
    #[DataProvider('sortArrayAlphabeticallyByValueProvider')]
    public function testSortArrayAlphabeticallyByValue(string $locale, array $values, array $expectedValues): void
    {
        $array = array_map(static fn (string $value) => ['text' => $value], $values);

        (new ArraySorterHelper())->sortArrayAlphabeticallyByValue('text', $array, $locale);

        $this->assertSame($expectedValues, array_column($array, 'text'));
    }
}
