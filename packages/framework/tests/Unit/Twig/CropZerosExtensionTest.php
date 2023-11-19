<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Twig\CropZerosExtension;

class CropZerosExtensionTest extends TestCase
{
    /**
     * @return array<int, array<'input'|'return', string>>
     */
    public function returnValuesProvider(): array
    {
        return [
            ['input' => '12', 'return' => '12'],
            ['input' => '12.00', 'return' => '12'],
            ['input' => '12,00', 'return' => '12'],
            ['input' => '12.630000', 'return' => '12.63'],
            ['input' => '12,630000', 'return' => '12,63'],
            ['input' => '1200', 'return' => '1200'],
        ];
    }

    /**
     * @dataProvider returnValuesProvider
     * @param mixed $input
     * @param mixed $return
     */
    public function testReturnValues(string $input, string $return): void
    {
        $this->assertSame($return, (new CropZerosExtension())->cropZeros($input));
    }
}
