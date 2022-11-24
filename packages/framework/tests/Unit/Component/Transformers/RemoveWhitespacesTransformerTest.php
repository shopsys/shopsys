<?php

namespace Tests\FrameworkBundle\Unit\Component\Transformers;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveWhitespacesTransformer;

class RemoveWhitespacesTransformerTest extends TestCase
{
    /**
     * @return array<int, array{value: null|string, expected: class-string<\foo>|null|string}>
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
     * @param string|class-string<\foo>|null $expected
     */
    public function testReverseTransform(?string $value, ?string $expected): void
    {
        $transformer = new RemoveWhitespacesTransformer();
        $this->assertSame($expected, $transformer->reverseTransform($value));
    }
}
