<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotApplicableException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorApplicability;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\BooleanFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\ChoiceFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\EntityFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\ProductFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class EntityFilterTest extends TestCase
{
    public function testRelatedEntitiesAreComparedByTheirIdentifiers(): void
    {
        $filter = $this->resolve(EntityFilter::new('author.publisher'));

        $condition = $filter->buildCondition($this->createRule(ExpressionOperatorEnum::IN, [new TestEntity(3), new TestEntity(5)]));

        $this->assertEquals(Condition::in('author.publisher.id', [3, 5]), $condition);
    }

    public function testMissingRelationIsTestedOnTheAssociationItself(): void
    {
        $filter = $this->resolve(EntityFilter::new('author.publisher'));

        $this->assertEquals(Condition::isNull('author.publisher'), $filter->buildCondition($this->createRule(ExpressionOperatorEnum::IS_NULL, null)));
    }

    public function testEntityClassIsLearntFromTheMedium(): void
    {
        $filter = $this->resolve(EntityFilter::new('author.publisher'));

        $this->assertSame(TestEntity::class, $filter->getValueFormOptions(ExpressionOperatorEnum::IN)['class']);
        $this->assertTrue($filter->getValueFormOptions(ExpressionOperatorEnum::IN)['multiple']);
    }

    public function testPathNotEndingWithAnAssociationIsRefused(): void
    {
        $this->expectException(FilterNotApplicableException::class);

        EntityFilter::new('name')->resolveFor($this->createEnvironment([
            'name' => new PathDescription('name', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::STRING),
        ]));
    }

    public function testUnknownEntityClassIsRefused(): void
    {
        $this->expectException(FilterNotApplicableException::class);

        EntityFilter::new('author.publisher')->resolveFor($this->createEnvironment([
            'author.publisher' => new PathDescription('author.publisher', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::ASSOCIATION),
        ]));
    }

    public function testProductFilterComparesTheSinglePickedProduct(): void
    {
        $filter = $this->resolve(ProductFilter::new('product'), 'product');

        $this->assertEquals(Condition::notEquals('product.id', 7), $filter->buildCondition($this->createRule(ExpressionOperatorEnum::NOT_EQUALS, new TestEntity(7))));
    }

    public function testChoiceFilterOffersItsChoicesAsMultipleSelectForList(): void
    {
        $filter = ChoiceFilter::new('status')->setChoices(['Pending' => 'pending', 'Approved' => 'approved']);
        $filter->resolveFor($this->createEnvironment());

        $this->assertTrue($filter->getValueFormOptions(ExpressionOperatorEnum::IN)['multiple']);
        $this->assertFalse($filter->getValueFormOptions(ExpressionOperatorEnum::EQUALS)['multiple']);
        $this->assertEquals(Condition::in('status', ['pending']), $filter->buildCondition($this->createRule(ExpressionOperatorEnum::IN, ['pending'])));
    }

    public function testBooleanFilterIsDecidedByItsOperationWithoutValue(): void
    {
        $filter = BooleanFilter::new('isVerifiedPurchase');
        $filter->resolveFor($this->createEnvironment());

        $this->assertSame([BooleanFilter::OPERATOR_YES, BooleanFilter::OPERATOR_NO], $filter->getOperators());
        $this->assertSame(ExpressionValueArityEnum::NONE, $filter->getValueArity(BooleanFilter::OPERATOR_YES));
        $this->assertEquals(Condition::equals('isVerifiedPurchase', true), $filter->buildCondition($this->createRule(BooleanFilter::OPERATOR_YES, null)));
        $this->assertEquals(Condition::equals('isVerifiedPurchase', false), $filter->buildCondition($this->createRule(BooleanFilter::OPERATOR_NO, 'stale')));
        $this->assertNull($filter->buildCondition($this->createRule(ExpressionOperatorEnum::EQUALS, true)));
    }

    private function resolve(EntityFilter $filter, string $path = 'author.publisher'): EntityFilter
    {
        $filter->resolveFor($this->createEnvironment([
            $path => new PathDescription($path, PathCardinalityEnum::TO_ONE, PathValueTypeEnum::ASSOCIATION, TestEntity::class),
        ]));

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
            new ExpressionOperatorApplicability(),
            new DateTimeZone('Europe/Prague'),
        );
    }

    private function createRule(string $operator, mixed $value): FilterRuleData
    {
        $rule = new FilterRuleData();
        $rule->operator = $operator;
        $rule->value = $value;

        return $rule;
    }
}

final class TestEntity
{
    public function __construct(
        private readonly int $id,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }
}
