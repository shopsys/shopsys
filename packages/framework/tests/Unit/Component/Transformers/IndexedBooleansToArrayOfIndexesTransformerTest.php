<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Transformers;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Form\Transformers\IndexedBooleansToArrayOfIndexesTransformer;

class IndexedBooleansToArrayOfIndexesTransformerTest extends TestCase
{
    /**
     * @return array<'expected'|'value', never[]>[]|array<'expected'|'value', int[]|bool[]>[]|array<'expected'|'value', string[]|array<'foo', bool>>[]|array<'expected'|'value', string|null>[]
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
     * @param mixed $value
     * @param mixed $expected
     */
    public function testTransform(string|array|null $value, ?array $expected): void
    {
        $transformer = new IndexedBooleansToArrayOfIndexesTransformer();
        $this->assertSame($expected, $transformer->transform($value));
    }

    /**
     * @return array<'expected'|'value', bool[]|int[]>[]|array<'expected'|'value', never[]|array<'foo', bool>|string[]>[]|array<'expected'|'value', string|null>[]
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
     * @param mixed $value
     * @param mixed $expected
     */
    public function testReverseTransform(string|array|null $value, ?array $expected): void
    {
        $transformer = new IndexedBooleansToArrayOfIndexesTransformer();
        $this->assertSame($expected, $transformer->reverseTransform($value));
    }
}
