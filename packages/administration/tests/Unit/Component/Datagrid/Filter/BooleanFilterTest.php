<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\BooleanFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class BooleanFilterTest extends TestCase
{
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

    private function createEnvironment(): FilterEnvironment
    {
        return new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions()),
            new ExpressionOperatorEnum(),
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
