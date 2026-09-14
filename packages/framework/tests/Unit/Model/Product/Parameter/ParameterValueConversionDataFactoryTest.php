<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\Parameter;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueConversionDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData;
use Tests\FrameworkBundle\Unit\TestCase;

class ParameterValueConversionDataFactoryTest extends TestCase
{
    /**
     * @return iterable<string, array{text: string, expectedNumericValue: string}>
     */
    public static function conversionProvider(): iterable
    {
        yield 'integer' => [
            'text' => '12',
            'expectedNumericValue' => '12',
        ];

        yield 'decimal with comma and unit' => [
            'text' => '12,5 kg',
            'expectedNumericValue' => '12.5',
        ];

        yield 'thousands with spaces' => [
            'text' => '1 200',
            'expectedNumericValue' => '1200',
        ];

        yield 'text without digits' => [
            'text' => 'abc',
            'expectedNumericValue' => '0',
        ];

        yield 'two decimal points' => [
            'text' => '1.2.3',
            'expectedNumericValue' => '0',
        ];
    }

    #[DataProvider('conversionProvider')]
    public function testCreateForNumericConversion(string $text, string $expectedNumericValue): void
    {
        $parameterValueData = new ParameterValueData();
        $parameterValueData->text = $text;
        $parameterValueData->locale = 'en';
        $parameterValue = new ParameterValue($parameterValueData);
        $this->setValueOfProtectedProperty($parameterValue, 'id', 1);

        $conversionData = (new ParameterValueConversionDataFactory())->createForNumericConversion([$parameterValue]);

        $this->assertSame($text, $conversionData[1]->oldValueText);
        $this->assertSame($expectedNumericValue, $conversionData[1]->newValueText);
    }
}
