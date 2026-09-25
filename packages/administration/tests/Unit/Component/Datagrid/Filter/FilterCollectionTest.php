<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter;

use DateTimeZone;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotFoundException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorApplicability;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterGroupData;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\NumericFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class FilterCollectionTest extends TestCase
{
    public function testFilterIsFoundByItsName(): void
    {
        $collection = new FilterCollection();
        $filter = TextFilter::new('author.fullName');

        $collection->add($filter);

        $this->assertTrue($collection->has('author_fullName'));
        $this->assertSame($filter, $collection->get('author_fullName'));
        $this->assertSame($filter, $collection->first());
        $this->assertFalse($collection->isEmpty());
    }

    public function testTwoFiltersOfOneNameAreRefused(): void
    {
        $collection = new FilterCollection();
        $collection->add(TextFilter::new('name'));

        $this->expectException(InvalidArgumentException::class);

        $collection->add(NumericFilter::new('name'));
    }

    public function testUnknownFilterIsRefused(): void
    {
        $this->expectException(FilterNotFoundException::class);

        new FilterCollection()->get('somethingElse');
    }

    public function testRulesOfGroupAreCombinedByTheOperatorOfTheGroupAndGroupsByTheOperatorOfTheForm(): void
    {
        $collection = $this->createResolvedCollection();
        $data = $this->createFormData(FilterGroupData::OPERATOR_OR, [
            [FilterGroupData::OPERATOR_AND, [
                $this->createRule('name', ExpressionOperatorEnum::CONTAINS, 'hrnek'),
                $this->createRule('price', ExpressionOperatorEnum::GREATER_THAN, 100),
            ]],
            [FilterGroupData::OPERATOR_AND, [
                $this->createRule('price', ExpressionOperatorEnum::BETWEEN, ['from' => 1, 'to' => 2]),
            ]],
        ]);

        $this->assertEquals(
            Condition::orX(
                Condition::andX(Condition::contains('name', 'hrnek'), Condition::greaterThan('price', 100)),
                Condition::between('price', 1, 2),
            ),
            $collection->createCondition($data),
        );
    }

    public function testRulesWithoutValueAndOfUnknownFiltersNarrowNothing(): void
    {
        $collection = $this->createResolvedCollection();
        $data = $this->createFormData(FilterGroupData::OPERATOR_AND, [
            [FilterGroupData::OPERATOR_AND, [
                $this->createRule('name', ExpressionOperatorEnum::CONTAINS, '   '),
                $this->createRule('somethingElse', ExpressionOperatorEnum::EQUALS, 'x'),
                $this->createRule('price', ExpressionOperatorEnum::BETWEEN, ['from' => 1, 'to' => null]),
            ]],
        ]);

        $this->assertNull($collection->createCondition($data));
    }

    public function testSingleRuleIsNotWrappedIntoGroup(): void
    {
        $collection = $this->createResolvedCollection();
        $data = $this->createFormData(FilterGroupData::OPERATOR_OR, [
            [FilterGroupData::OPERATOR_OR, [$this->createRule('price', ExpressionOperatorEnum::IS_NULL, null)]],
        ]);

        $this->assertEquals(Condition::isNull('price'), $collection->createCondition($data));
    }

    private function createResolvedCollection(): FilterCollection
    {
        $collection = new FilterCollection();
        $collection
            ->add(TextFilter::new('name'))
            ->add(NumericFilter::new('price'));
        $collection->resolveFor(new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class)),
            new ExpressionOperatorEnum(),
            new ExpressionOperatorApplicability(),
            new DateTimeZone('Europe/Prague'),
        ));

        return $collection;
    }

    /**
     * @param list<array{string, list<\Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData>}> $groups
     */
    private function createFormData(string $operator, array $groups): FilterFormData
    {
        $data = new FilterFormData();
        $data->operator = $operator;

        foreach ($groups as [$groupOperator, $rules]) {
            $group = new FilterGroupData();
            $group->operator = $groupOperator;
            $group->rules = $rules;
            $data->groups[] = $group;
        }

        return $data;
    }

    private function createRule(string $filter, string $operator, mixed $value): FilterRuleData
    {
        $rule = new FilterRuleData();
        $rule->filter = $filter;
        $rule->operator = $operator;
        $rule->value = $value;

        return $rule;
    }
}
