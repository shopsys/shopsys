<?php

namespace Tests\FrameworkBundle\Unit\Component\Transformers;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer;

class RemoveDuplicatesFromArrayTransformerTest extends TestCase
{
    public function testReverseTransform(): void
    {
        $array = ['a', 'b', 'a'];

        $transformer = new RemoveDuplicatesFromArrayTransformer();
        $this->assertSame(['a', 'b'], $transformer->reverseTransform($array));
    }

    public function testReverseTransformPresevesKeys(): void
    {
        $array = [0 => 'a', 10 => 'b', 20 => 'a'];

        $transformer = new RemoveDuplicatesFromArrayTransformer();
        $this->assertSame([0 => 'a', 10 => 'b'], $transformer->reverseTransform($array));
    }

    public function testReverseTransformComparesStrictly(): void
    {
        $array = ['0', 0, null, false];

        $transformer = new RemoveDuplicatesFromArrayTransformer();
        $this->assertSame(['0', 0, null, false], $transformer->reverseTransform($array));
    }
}
