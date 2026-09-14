<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotApplicableException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\NumericFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class AbstractFilterTest extends TestCase
{
    public function testNameAndLabelAreDerivedFromThePath(): void
    {
        $filter = TextFilter::new('author.fullName');

        $this->assertSame('author_fullName', $filter->getName());
        $this->assertSame('Full name', $filter->getLabel());
        $this->assertSame('Name', TextFilter::new('name')->getLabel());
    }

    public function testValueIsEnteredAsTextUnlessTheTypeSaysOtherwise(): void
    {
        $filter = $this->createResolvedFilter(TextFilter::new('name'));

        $this->assertSame(TextType::class, $filter->getValueFormType(ExpressionValueArityEnum::SINGLE));
    }

    public function testNameAndLabelCanBeGiven(): void
    {
        $filter = TextFilter::new('author.fullName', 'Author')->setName('author');

        $this->assertSame('author', $filter->getName());
        $this->assertSame('Author', $filter->getLabel());
    }

    public function testOperatorsAreNarrowedToThoseApplicableToThePath(): void
    {
        $filter = NumericFilter::new('price');

        $filter->resolveFor($this->createEnvironment([
            'price' => new PathDescription('price', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::INTEGER),
        ]));

        $this->assertNotContains(ExpressionOperatorEnum::IS_EMPTY, $filter->getOperators());
        $this->assertContains(ExpressionOperatorEnum::BETWEEN, $filter->getOperators());
        $this->assertSame(IntegerType::class, $filter->getValueFormType(ExpressionValueArityEnum::SINGLE));
    }

    public function testOperatorsCanBeRestrictedByTheDeclaration(): void
    {
        $filter = TextFilter::new('name')->setOperators([ExpressionOperatorEnum::EQUALS]);

        $filter->resolveFor($this->createEnvironment());

        $this->assertSame([ExpressionOperatorEnum::EQUALS], $filter->getOperators());
    }

    /**
     * The type knows how to enter and translate only its own operations, so the declaration cannot add another.
     */
    public function testOperationNotOfferedByTheTypeCannotBeSet(): void
    {
        $this->expectException(FilterNotApplicableException::class);
        $this->expectExceptionMessage('"isEmpty" cannot be set');

        TextFilter::new('name')->setOperators([ExpressionOperatorEnum::EQUALS, ExpressionOperatorEnum::IS_EMPTY]);
    }

    public function testTextOperationsAreDroppedOnNumberAndTheRestStays(): void
    {
        $filter = TextFilter::new('price');

        $filter->resolveFor($this->createEnvironment([
            'price' => new PathDescription('price', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::DECIMAL),
        ]));

        $this->assertSame(
            [ExpressionOperatorEnum::EQUALS, ExpressionOperatorEnum::NOT_EQUALS, ExpressionOperatorEnum::IS_NULL, ExpressionOperatorEnum::IS_NOT_NULL],
            $filter->getOperators(),
        );
    }

    public function testFilterWithNoApplicableOperationIsRefused(): void
    {
        $filter = TextFilter::new('items')->setOperators([ExpressionOperatorEnum::CONTAINS]);

        $this->expectException(FilterNotApplicableException::class);

        $filter->resolveFor($this->createEnvironment([
            'items' => new PathDescription('items', PathCardinalityEnum::TO_MANY, PathValueTypeEnum::ASSOCIATION),
        ]));
    }

    public function testFilterOverAnUnknownPathIsRefused(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class), [], false);

        $this->expectException(FilterNotApplicableException::class);
        $this->expectExceptionMessage('nope');

        TextFilter::new('nope')->resolveFor(new FilterEnvironment($adapter, new ExpressionOperatorEnum(), new DateTimeZone('UTC')));
    }

    public function testRuleIsTranslatedIntoComparisonOfThePath(): void
    {
        $filter = $this->createResolvedFilter(TextFilter::new('author.fullName'));

        $this->assertEquals(
            Condition::contains('author.fullName', 'novak'),
            $filter->buildCondition($this->createRule(ExpressionOperatorEnum::CONTAINS, '  novak ')),
        );
    }

    /**
     * "Items is not X" means that no item is X — negating the operation itself would match a record with any
     * other item as well.
     */
    public function testNegationOverCollectionNegatesTheWholeCondition(): void
    {
        $filter = TextFilter::new('items.name');
        $filter->resolveFor($this->createEnvironment([
            'items.name' => new PathDescription('items.name', PathCardinalityEnum::TO_MANY, PathValueTypeEnum::STRING),
        ]));

        $this->assertEquals(
            Condition::not(Condition::equals('items.name', 'X')),
            $filter->buildCondition($this->createRule(ExpressionOperatorEnum::NOT_EQUALS, 'X')),
        );
        $this->assertEquals(
            Condition::contains('items.name', 'X'),
            $filter->buildCondition($this->createRule(ExpressionOperatorEnum::CONTAINS, 'X')),
        );
    }

    public function testNegationOverSingleValueStaysTheNegatingOperation(): void
    {
        $filter = $this->createResolvedFilter(TextFilter::new('name'));

        $this->assertEquals(
            Condition::notEquals('name', 'X'),
            $filter->buildCondition($this->createRule(ExpressionOperatorEnum::NOT_EQUALS, 'X')),
        );
    }

    public function testRuleOfAnOperationNotOfferedNarrowsNothing(): void
    {
        $filter = $this->createResolvedFilter(TextFilter::new('name')->setOperators([ExpressionOperatorEnum::EQUALS]));

        $this->assertNull($filter->buildCondition($this->createRule(ExpressionOperatorEnum::CONTAINS, 'x')));
        $this->assertNull($filter->buildCondition($this->createRule(null, 'x')));
    }

    public function testValueIsShapedByTheArityOfTheOperation(): void
    {
        $filter = $this->createResolvedFilter(NumericFilter::new('price'));

        $this->assertEquals(Condition::between('price', 1, 5), $filter->buildCondition($this->createRule(ExpressionOperatorEnum::BETWEEN, ['from' => 1, 'to' => 5])));
        $this->assertEquals(Condition::isNull('price'), $filter->buildCondition($this->createRule(ExpressionOperatorEnum::IS_NULL, 'ignored')));
        $this->assertNull($filter->buildCondition($this->createRule(ExpressionOperatorEnum::BETWEEN, ['from' => 1])));
        $this->assertNull($filter->buildCondition($this->createRule(ExpressionOperatorEnum::EQUALS, null)));
    }

    public function testValueFormOptionsOfTheDeclarationWin(): void
    {
        $filter = $this->createResolvedFilter(NumericFilter::new('price')->setValueFormOptions(['scale' => 2, 'required' => true]));

        $options = $filter->getValueFormOptions(ExpressionValueArityEnum::SINGLE);

        $this->assertSame(NumberType::class, $filter->getValueFormType(ExpressionValueArityEnum::SINGLE));
        $this->assertSame(2, $options['scale']);
        $this->assertTrue($options['required']);
    }

    private function createResolvedFilter(FilterInterface $filter): FilterInterface
    {
        $filter->resolveFor($this->createEnvironment());

        return $filter;
    }

    /**
     * @param array<string, \Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription> $pathDescriptions
     */
    private function createEnvironment(array $pathDescriptions = []): FilterEnvironment
    {
        return new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class), $pathDescriptions),
            new ExpressionOperatorEnum(),
            new DateTimeZone('Europe/Prague'),
        );
    }

    private function createRule(?string $operator, mixed $value): FilterRuleData
    {
        $rule = new FilterRuleData();
        $rule->filter = 'irrelevant';
        $rule->operator = $operator;
        $rule->value = $value;

        return $rule;
    }
}
