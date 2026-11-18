<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\MoneyFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class MoneyFilterTest extends TestCase
{
    /**
     * Amounts are stored with six decimal places, so the input keeps them all instead of rounding to two.
     */
    public function testAmountIsEnteredAsMoneyWithAllTheDecimalPlacesOfTheColumn(): void
    {
        $filter = MoneyFilter::new('totalPrice');
        $filter->resolveFor(new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class), [
                'totalPrice' => new PathDescription('totalPrice', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::MONEY),
            ]),
            new ExpressionOperatorEnum(),
            new DateTimeZone('Europe/Prague'),
        ));

        foreach ([ExpressionValueArityEnum::SINGLE, ExpressionValueArityEnum::RANGE] as $arity) {
            $this->assertSame(MoneyType::class, $filter->getValueFormType($arity));
            $this->assertSame(6, $filter->getValueFormOptions($arity)['scale']);
        }
    }
}
