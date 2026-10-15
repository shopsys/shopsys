<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Field;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\UrlField;

class UrlFieldTest extends TestCase
{
    /**
     * @return iterable<string, array{value: mixed, expectedResult: mixed}>
     */
    public static function getNormalizeValueData(): iterable
    {
        yield 'https url is kept' => [
            'value' => ' https://example.com/a?b=1 ',
            'expectedResult' => 'https://example.com/a?b=1',
        ];

        yield 'relative url is kept' => [
            'value' => '/contact',
            'expectedResult' => '/contact',
        ];

        yield 'mailto is kept' => [
            'value' => 'mailto:info@example.com',
            'expectedResult' => 'mailto:info@example.com',
        ];

        yield 'tel is kept' => [
            'value' => 'tel:+420123456789',
            'expectedResult' => 'tel:+420123456789',
        ];

        yield 'anchor is kept' => [
            'value' => '#top',
            'expectedResult' => '#top',
        ];

        yield 'javascript scheme becomes null' => [
            'value' => 'javascript:alert(1)',
            'expectedResult' => null,
        ];

        yield 'protocol-relative url becomes null' => [
            'value' => '//evil.com',
            'expectedResult' => null,
        ];

        yield 'variable is rejected unless allowed' => [
            'value' => '{product_url}',
            'expectedResult' => null,
        ];

        yield 'empty string becomes null' => [
            'value' => '',
            'expectedResult' => null,
        ];

        yield 'non-string becomes null' => [
            'value' => 5,
            'expectedResult' => null,
        ];
    }

    #[DataProvider('getNormalizeValueData')]
    public function testNormalizeValue(mixed $value, mixed $expectedResult): void
    {
        $field = UrlField::create('link');

        $this->assertSame($expectedResult, $field->normalizeValue($value));
    }

    public function testVariableIsKeptWhenAllowed(): void
    {
        $field = UrlField::create('link')->allowVariables();

        $this->assertSame('{product_url}', $field->normalizeValue('{product_url}'));
        $this->assertSame('{product_url}?a=1', $field->normalizeValue('{product_url}?a=1'));
        $this->assertNull($field->normalizeValue('{bad var}'));
        $this->assertSame('mailto:info@example.com', $field->normalizeValue('mailto:info@example.com'));
        $this->assertSame('tel:+420123456789', $field->normalizeValue('tel:+420123456789'));
    }
}
