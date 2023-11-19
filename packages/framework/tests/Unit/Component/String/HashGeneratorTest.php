<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\String;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;

class HashGeneratorTest extends TestCase
{
    /**
     * @return int[][]
     */
    public function hashLengthProvider(): array
    {
        return [
            [1],
            [13],
            [100],
        ];
    }

    /**
     * @dataProvider hashLengthProvider
     * @param mixed $length
     */
    public function testGenerateHash(int $length): void
    {
        $hashGererator = new HashGenerator();

        $hash = $hashGererator->generateHash($length);

        $this->assertSame($length, strlen($hash));
    }
}
