<?php

declare(strict_types=1);

namespace Tests\App\Unit;

use PHPUnit\Framework\TestCase;

class NumberFormattingTest extends TestCase
{
    public function testNumberFormatting(): void
    {
        $formattedNumber = sprintf('%01.2f', 123456.789);
        $expectedResult = '123456.79';

        $this->assertSame($expectedResult, $formattedNumber);
    }
}
