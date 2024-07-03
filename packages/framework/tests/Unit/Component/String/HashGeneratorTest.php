<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\String;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;

class HashGeneratorTest extends TestCase
{
    public static function hashLengthProvider()
    {
        return [
            [1],
            [13],
            [100],
        ];
    }

    /**
     * @param mixed $length
     */
    #[DataProvider('hashLengthProvider')]
    public function testGenerateHash($length)
    {
        $hashGererator = new HashGenerator();

        $hash = $hashGererator->generateHash($length);

        $this->assertSame($length, strlen($hash));
    }
}
