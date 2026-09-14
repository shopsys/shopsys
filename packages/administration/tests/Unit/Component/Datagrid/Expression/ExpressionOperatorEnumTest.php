<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Expression;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotApplicableToPathException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Component\Enum\InvalidEnumCaseException;

final class ExpressionOperatorEnumTest extends TestCase
{
    /**
     * @return array<string, array{string, \Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum, \Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum, bool}>
     */
    public static function applicabilityDataProvider(): array
    {
        return [
            'text search on text' => [ExpressionOperatorEnum::CONTAINS, PathValueTypeEnum::STRING, PathCardinalityEnum::TO_ONE, true],
            'text search on a value of unknown type' => [ExpressionOperatorEnum::STARTS_WITH, PathValueTypeEnum::UNKNOWN, PathCardinalityEnum::TO_ONE, false],
            'equality on a value of unknown type' => [ExpressionOperatorEnum::EQUALS, PathValueTypeEnum::UNKNOWN, PathCardinalityEnum::TO_ONE, true],
            'text search on a date' => [ExpressionOperatorEnum::CONTAINS, PathValueTypeEnum::DATETIME, PathCardinalityEnum::TO_ONE, false],
            'text search on a number' => [ExpressionOperatorEnum::ENDS_WITH, PathValueTypeEnum::INTEGER, PathCardinalityEnum::TO_ONE, false],
            'text search on an association' => [ExpressionOperatorEnum::CONTAINS, PathValueTypeEnum::ASSOCIATION, PathCardinalityEnum::TO_ONE, false],
            'null test on a single value' => [ExpressionOperatorEnum::IS_NULL, PathValueTypeEnum::STRING, PathCardinalityEnum::TO_ONE, true],
            'null test on a collection' => [ExpressionOperatorEnum::IS_NOT_NULL, PathValueTypeEnum::STRING, PathCardinalityEnum::TO_MANY, false],
            'emptiness test on a collection' => [ExpressionOperatorEnum::IS_EMPTY, PathValueTypeEnum::ASSOCIATION, PathCardinalityEnum::TO_MANY, true],
            'emptiness test on a single value' => [ExpressionOperatorEnum::IS_NOT_EMPTY, PathValueTypeEnum::STRING, PathCardinalityEnum::TO_ONE, false],
            'comparison on a collection is allowed' => [ExpressionOperatorEnum::GREATER_THAN, PathValueTypeEnum::DECIMAL, PathCardinalityEnum::TO_MANY, true],
            'equality on a date' => [ExpressionOperatorEnum::EQUALS, PathValueTypeEnum::DATE, PathCardinalityEnum::TO_ONE, true],
        ];
    }

    #[DataProvider('applicabilityDataProvider')]
    public function testOperatorIsApplicableOnlyWhereItCannotGiveWrongAnswer(
        string $operator,
        PathValueTypeEnum $valueType,
        PathCardinalityEnum $cardinality,
        bool $expectedApplicable,
    ): void {
        $pathDescription = new PathDescription('path', $cardinality, $valueType);

        if ($expectedApplicable === false) {
            $this->expectException(OperatorNotApplicableToPathException::class);
        }

        (new ExpressionOperatorEnum())->assertApplicable($operator, $pathDescription);
        $this->addToAssertionCount(1);
    }

    public function testInapplicableOperatorIsRefusedWithTheReason(): void
    {
        $pathDescription = new PathDescription('items.name', PathCardinalityEnum::TO_MANY, PathValueTypeEnum::STRING);

        $this->expectException(OperatorNotApplicableToPathException::class);
        $this->expectExceptionMessage('Operator "isNull" cannot be applied to path "items.name": a collection has no null to test');

        (new ExpressionOperatorEnum())->assertApplicable(ExpressionOperatorEnum::IS_NULL, $pathDescription);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function valueArityDataProvider(): array
    {
        return [
            ExpressionOperatorEnum::IS_NULL => [ExpressionOperatorEnum::IS_NULL, ExpressionValueArityEnum::NONE],
            ExpressionOperatorEnum::IS_NOT_EMPTY => [ExpressionOperatorEnum::IS_NOT_EMPTY, ExpressionValueArityEnum::NONE],
            ExpressionOperatorEnum::EQUALS => [ExpressionOperatorEnum::EQUALS, ExpressionValueArityEnum::SINGLE],
            ExpressionOperatorEnum::CONTAINS => [ExpressionOperatorEnum::CONTAINS, ExpressionValueArityEnum::SINGLE],
            ExpressionOperatorEnum::NOT_IN => [ExpressionOperatorEnum::NOT_IN, ExpressionValueArityEnum::LIST],
            ExpressionOperatorEnum::BETWEEN => [ExpressionOperatorEnum::BETWEEN, ExpressionValueArityEnum::RANGE],
        ];
    }

    #[DataProvider('valueArityDataProvider')]
    public function testOperatorComparesValueOfItsArity(string $operator, string $expectedArity): void
    {
        $this->assertSame($expectedArity, (new ExpressionOperatorEnum())->getValueArity($operator));
    }

    public function testArityOfUnknownOperatorIsRefused(): void
    {
        $this->expectException(InvalidEnumCaseException::class);

        (new ExpressionOperatorEnum())->getValueArity('somethingElse');
    }
}
