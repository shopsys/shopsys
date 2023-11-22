<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Transformers;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveWhitespacesTransformer;

class RemoveWhitespacesTransformerTest extends TestCase
{
    /**
     * @return array<'expected'|'value', string>[]|array<'expected'|'value', null>[]
     */
    public function transformValuesProvider(): array
    {
        return [
            ['value' => 'foo bar', 'expected' => 'foobar'],
            ['value' => 'FooBar', 'expected' => 'FooBar'],
            ['value' => '  foo  bar  ', 'expected' => 'foobar'],
            ['value' => "foo\t", 'expected' => 'foo'],
            ['value' => "fo\no", 'expected' => 'foo'],
            ['value' => null, 'expected' => null],
        ];
    }

    /**
     * @dataProvider transformValuesProvider
     * @param string|null $value
     * @param string|null $expected
     */
    public function testReverseTransform(?string $value, ?string $expected): void
    {
        $transformer = new RemoveWhitespacesTransformer();
        $this->assertSame($expected, $transformer->reverseTransform($value));
    }
}
