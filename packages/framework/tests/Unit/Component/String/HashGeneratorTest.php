<?php

namespace Tests\FrameworkBundle\Unit\Component\String;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;

class HashGeneratorTest extends TestCase
{
    public function hashLengthProvider()
    {
        return [
            [1],
            [13],
            [100],
        ];
    }

    /**
     * @dataProvider hashLengthProvider
     */
    public function testGenerateHash($length): void
    {
        $hashGererator = new HashGenerator();

        $hash = $hashGererator->generateHash($length);

        $this->assertSame($length, strlen($hash));
    }
}
