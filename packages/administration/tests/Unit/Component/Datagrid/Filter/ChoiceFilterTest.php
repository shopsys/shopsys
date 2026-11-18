<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\ChoiceFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class ChoiceFilterTest extends TestCase
{
    use FilterRuleTrait;

    public function testChoiceFilterOffersItsChoicesAsMultipleSelectForList(): void
    {
        $filter = ChoiceFilter::new('status')->setChoices([
            'Pending' => 'pending',
            'Approved' => 'approved',
        ]);
        $filter->resolveFor($this->createEnvironment());

        $this->assertTrue($filter->getValueFormOptions(ExpressionValueArityEnum::LIST)['multiple']);
        $this->assertFalse($filter->getValueFormOptions(ExpressionValueArityEnum::SINGLE)['multiple']);
        $this->assertEquals(Condition::in('status', ['pending']), $filter->buildCondition($this->createRule(ExpressionOperatorEnum::IN, ['pending'])));
    }

    private function createEnvironment(): FilterEnvironment
    {
        return new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class)),
            new ExpressionOperatorEnum(),
            new DateTimeZone('Europe/Prague'),
        );
    }
}
