<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Twig;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Twig\CropZerosExtension;

class CropZerosExtensionTest extends TestCase
{
    public static function returnValuesProvider(): array
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

    #[DataProvider('returnValuesProvider')]
    public function testReturnValues(mixed $input, mixed $return): void
    {
        $this->assertSame($return, (new CropZerosExtension())->cropZeros($input));
    }
}
