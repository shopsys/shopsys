<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Bytes;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Bytes\BytesHelper;

class BytesHelperTest extends TestCase
{
    /**
     * @dataProvider phpStringBytesToBytesDataProvider
     * @param string $phpStringBytes
     * @param int $expectedBytes
     */
    public function testConvertPhpStringByteDefinitionToBytes(string $phpStringBytes, int $expectedBytes): void
    {
        $this->assertSame($expectedBytes, BytesHelper::convertPhpStringByteDefinitionToBytes($phpStringBytes));
    }

    /**
     * @return array[]
     */
    public function phpStringBytesToBytesDataProvider(): array
    {
        return [
            ['phpStringBytes' => '-1', 'expectedBytes' => -1],
            ['phpStringBytes' => '12', 'expectedBytes' => 12],
            ['phpStringBytes' => '12K', 'expectedBytes' => 12288],
            ['phpStringBytes' => '12M', 'expectedBytes' => 12582912],
            ['phpStringBytes' => '12G', 'expectedBytes' => 12884901888],
            ['phpStringBytes' => '12T', 'expectedBytes' => 13194139533312],
        ];
    }
}
