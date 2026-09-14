<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterPanel;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterGroupData;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\NumericFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Symfony\Component\Form\FormInterface;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class FilterPanelTest extends TestCase
{
    public function testRulesOfGroupAreCombinedByTheOperatorOfTheGroupAndGroupsByTheOperatorOfTheForm(): void
    {
        $filterPanel = $this->createFilterPanel($this->createFormData(FilterGroupData::OPERATOR_OR, [
            [FilterGroupData::OPERATOR_AND, [
                $this->createRule('name', ExpressionOperatorEnum::CONTAINS, 'hrnek'),
                $this->createRule('price', ExpressionOperatorEnum::GREATER_THAN, 100),
            ]],
            [FilterGroupData::OPERATOR_AND, [
                $this->createRule('price', ExpressionOperatorEnum::BETWEEN, ['from' => 1, 'to' => 2]),
            ]],
        ]));

        $this->assertTrue($filterPanel->hasRules());
        $this->assertEquals(
            Condition::orX(
                Condition::andX(Condition::contains('name', 'hrnek'), Condition::greaterThan('price', 100)),
                Condition::between('price', 1, 2),
            ),
            $filterPanel->createCondition(),
        );
    }

    public function testRulesWithoutValueAndOfUnknownFiltersNarrowNothing(): void
    {
        $filterPanel = $this->createFilterPanel($this->createFormData(FilterGroupData::OPERATOR_AND, [
            [FilterGroupData::OPERATOR_AND, [
                $this->createRule('name', ExpressionOperatorEnum::CONTAINS, '   '),
                $this->createRule('somethingElse', ExpressionOperatorEnum::EQUALS, 'x'),
                $this->createRule('price', ExpressionOperatorEnum::BETWEEN, ['from' => 1, 'to' => null]),
            ]],
        ]));

        $this->assertTrue($filterPanel->hasRules());
        $this->assertNull($filterPanel->createCondition());
    }

    public function testSingleRuleIsNotWrappedIntoGroup(): void
    {
        $filterPanel = $this->createFilterPanel($this->createFormData(FilterGroupData::OPERATOR_OR, [
            [FilterGroupData::OPERATOR_OR, [$this->createRule('price', ExpressionOperatorEnum::IS_NULL, null)]],
        ]));

        $this->assertEquals(Condition::isNull('price'), $filterPanel->createCondition());
    }

    /**
     * An invalid filter shows its errors instead of quietly listing more than the administrator asked for.
     */
    public function testInvalidFormNarrowsNothing(): void
    {
        $filterPanel = $this->createFilterPanel($this->createFormData(FilterGroupData::OPERATOR_AND, [
            [FilterGroupData::OPERATOR_AND, [$this->createRule('name', ExpressionOperatorEnum::CONTAINS, 'hrnek')]],
        ]), false);

        $this->assertTrue($filterPanel->hasRules());
        $this->assertNull($filterPanel->createCondition());
    }

    /**
     * A form never submitted is only the starting point it offers, so it is never asked whether it is valid.
     */
    public function testUnsubmittedFormHasNoRules(): void
    {
        $formMock = $this->createMock(FormInterface::class);
        $formMock->method('isSubmitted')->willReturn(false);
        $formMock->expects($this->never())->method('isValid');

        $filterPanel = new FilterPanel($formMock, $this->createStub(FormInterface::class), $this->createResolvedFilters());

        $this->assertFalse($filterPanel->hasRules());
        $this->assertNull($filterPanel->createCondition());
    }

    public function testFormSubmittedWithoutAnyRuleHasNoRules(): void
    {
        $filterPanel = $this->createFilterPanel($this->createFormData(FilterGroupData::OPERATOR_AND, [
            [FilterGroupData::OPERATOR_AND, []],
        ]));

        $this->assertFalse($filterPanel->hasRules());
        $this->assertNull($filterPanel->createCondition());
    }

    private function createFilterPanel(FilterFormData $data, bool $valid = true): FilterPanel
    {
        $formStub = $this->createStub(FormInterface::class);
        $formStub->method('isSubmitted')->willReturn(true);
        $formStub->method('isValid')->willReturn($valid);
        $formStub->method('getData')->willReturn($data);

        return new FilterPanel($formStub, $this->createStub(FormInterface::class), $this->createResolvedFilters());
    }

    private function createResolvedFilters(): FilterCollection
    {
        $environment = new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions()),
            new ExpressionOperatorEnum(),
            new DateTimeZone('Europe/Prague'),
        );
        $filters = new FilterCollection();

        foreach ([TextFilter::new('name'), NumericFilter::new('price')] as $filter) {
            $filter->resolveFor($environment);
            $filters->add($filter);
        }

        return $filters;
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
