<?php

namespace Tests\FrameworkBundle\Unit\Component\Transformers;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Form\Transformers\IndexedBooleansToArrayOfIndexesTransformer;

class IndexedBooleansToArrayOfIndexesTransformerTest extends TestCase
{
    /**
     * @return array<int, array{value: class-string<\foo>|array<class-string<\foo>>|int[]|null, expected: array<int|string, bool>|null}>
     */
    public function transformValuesProvider(): array
    {
        return [
            ['value' => [], 'expected' => []],
            ['value' => [1, 2, 3], 'expected' => [1 => true, 2 => true, 3 => true]],
            ['value' => ['foo'], 'expected' => ['foo' => true]],
            ['value' => 'foo', 'expected' => null],
            ['value' => null, 'expected' => null],
        ];
    }

    /**
     * @dataProvider transformValuesProvider
     * @param mixed[]|int[]|array<class-string<\foo>>|class-string<\foo>|null $value
     * @param array<int|string, true>|null $expected
     */
    public function testTransform(array|string|null $value, ?array $expected): void
    {
        $transformer = new IndexedBooleansToArrayOfIndexesTransformer();
        $this->assertSame($expected, $transformer->transform($value));
    }

    /**
     * @return array<int, array{value: class-string<\foo>|array<int|string, bool>|null, expected: array<class-string<\foo>>|int[]|null}>
     */
    public function reverseTransformValuesProvider(): array
    {
        return [
            ['value' => [], 'expected' => []],
            ['value' => [1 => true, 2 => true, 3 => true], 'expected' => [1, 2, 3]],
            ['value' => [1 => false, 2 => true, 3 => false], 'expected' => [2]],
            ['value' => ['foo' => true], 'expected' => ['foo']],
            ['value' => ['foo' => false], 'expected' => []],
            ['value' => 'foo', 'expected' => null],
            ['value' => null, 'expected' => null],
        ];
    }

    /**
     * @dataProvider reverseTransformValuesProvider
     * @param class-string<\foo>|array<int|string, bool>|null $value
     * @param mixed[]|int[]|array<class-string<\foo>>|null $expected
     */
    public function testReverseTransform(string|array|null $value, ?array $expected): void
    {
        $transformer = new IndexedBooleansToArrayOfIndexesTransformer();
        $this->assertSame($expected, $transformer->reverseTransform($value));
    }
}
