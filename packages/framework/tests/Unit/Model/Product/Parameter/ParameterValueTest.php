<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\Parameter;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData;

class ParameterValueTest extends TestCase
{
    /**
     * @return iterable<string, array{numericValue: string|null}>
     */
    public static function acceptedNumericValueProvider(): iterable
    {
        yield 'null' => [
            'numericValue' => null,
        ];

        yield 'integer string' => [
            'numericValue' => '12',
        ];

        yield 'decimal string' => [
            'numericValue' => '12.5',
        ];
    }

    /**
     * @return iterable<string, array{numericValue: string}>
     */
    public static function rejectedNumericValueProvider(): iterable
    {
        yield 'empty string' => [
            'numericValue' => '',
        ];

        yield 'text' => [
            'numericValue' => 'abc',
        ];

        yield 'two decimal points' => [
            'numericValue' => '1.2.3',
        ];
    }

    #[DataProvider('acceptedNumericValueProvider')]
    public function testAcceptedNumericValueIsStored(?string $numericValue): void
    {
        $parameterValue = new ParameterValue($this->createParameterValueData($numericValue));

        $this->assertSame($numericValue, $parameterValue->getNumericValue());
    }

    #[DataProvider('rejectedNumericValueProvider')]
    public function testRejectedNumericValueThrowsOnConstruct(string $numericValue): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ParameterValue($this->createParameterValueData($numericValue));
    }

    #[DataProvider('rejectedNumericValueProvider')]
    public function testRejectedNumericValueThrowsOnEdit(string $numericValue): void
    {
        $parameterValue = new ParameterValue($this->createParameterValueData('1'));

        $this->expectException(InvalidArgumentException::class);

        $parameterValue->edit($this->createParameterValueData($numericValue));
    }

    protected function createParameterValueData(?string $numericValue): ParameterValueData
    {
        $parameterValueData = new ParameterValueData();
        $parameterValueData->text = 'text';
        $parameterValueData->locale = 'en';
        $parameterValueData->numericValue = $numericValue;

        return $parameterValueData;
    }
}
