<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Transformers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Form\Transformers\IndexedBooleansToArrayOfIndexesTransformer;

class IndexedBooleansToArrayOfIndexesTransformerTest extends TestCase
{
    public static function transformValuesProvider(): array
    {
        return [
            ['value' => [], 'expected' => []],
            ['value' => [1, 2, 3], 'expected' => [1 => true, 2 => true, 3 => true]],
            ['value' => ['foo'], 'expected' => ['foo' => true]],
            ['value' => 'foo', 'expected' => null],
            ['value' => null, 'expected' => null],
        ];
    }

    #[DataProvider('transformValuesProvider')]
    public function testTransform(mixed $value, mixed $expected): void
    {
        $transformer = new IndexedBooleansToArrayOfIndexesTransformer();
        $this->assertSame($expected, $transformer->transform($value));
    }

    public static function reverseTransformValuesProvider(): array
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

    #[DataProvider('reverseTransformValuesProvider')]
    public function testReverseTransform(mixed $value, mixed $expected): void
    {
        $transformer = new IndexedBooleansToArrayOfIndexesTransformer();
        $this->assertSame($expected, $transformer->reverseTransform($value));
    }
}
