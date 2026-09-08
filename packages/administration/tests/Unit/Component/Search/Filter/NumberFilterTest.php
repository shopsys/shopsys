<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Search\Filter;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Search\Filter\MoneyFilter;
use Shopsys\AdministrationBundle\Component\Search\Filter\NumberFilter;
use Shopsys\AdministrationBundle\Component\Search\FilterRule;
use Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection;
use Shopsys\AdministrationBundle\Component\Search\Operator;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\ProxyQueryFactoryTrait;

final class NumberFilterTest extends TestCase
{
    use ProxyQueryFactoryTrait;

    /**
     * @return iterable<string, array{operator: \Shopsys\AdministrationBundle\Component\Search\Operator, expectedCondition: string}>
     */
    public static function getOperatorData(): iterable
    {
        yield 'is compares with equals' => ['operator' => Operator::IS, 'expectedCondition' => 'o.id = :advancedSearch_id_0_value'];

        yield 'not compares with not-equals and matches null' => ['operator' => Operator::IS_NOT, 'expectedCondition' => '(o.id != :advancedSearch_id_0_value OR o.id IS NULL)'];

        yield 'higher than' => ['operator' => Operator::GT, 'expectedCondition' => 'o.id > :advancedSearch_id_0_value'];

        yield 'higher or equal' => ['operator' => Operator::GTE, 'expectedCondition' => 'o.id >= :advancedSearch_id_0_value'];

        yield 'lower than' => ['operator' => Operator::LT, 'expectedCondition' => 'o.id < :advancedSearch_id_0_value'];

        yield 'lower or equal' => ['operator' => Operator::LTE, 'expectedCondition' => 'o.id <= :advancedSearch_id_0_value'];
    }

    #[DataProvider('getOperatorData')]
    public function testComparisonOperatorsBuildExpectedCondition(Operator $operator, string $expectedCondition): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = NumberFilter::create('id', 'ID');
        $filter->setProxyQuery($proxyQuery);
        $rule = new FilterRule($operator, 42.0, '0', 'advancedSearch_id');

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), new FilterRuleCollection([$rule]));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertSame(
            $expectedCondition,
            (string)$queryBuilder->getDQLPart('where'),
        );
        $this->assertSame(42.0, $queryBuilder->getParameter('advancedSearch_id_0_value')->getValue());
    }

    public function testUnsupportedOperatorThrowsException(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = NumberFilter::create('id', 'ID');
        $filter->setProxyQuery($proxyQuery);
        $rule = new FilterRule(Operator::CONTAINS, 42, '0', 'advancedSearch_id');

        $this->expectException(InvalidArgumentException::class);

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), new FilterRuleCollection([$rule]));
    }

    public function testScaleIsConfigurable(): void
    {
        $this->assertSame([], NumberFilter::create('id', 'ID')->getValueFormOptions());
        $this->assertSame(['scale' => 3], NumberFilter::create('id', 'ID')->withScale(3)->getValueFormOptions());
    }

    public function testMoneyFilterDefaultsToTwoDecimalPlaces(): void
    {
        $this->assertSame(['scale' => 2], MoneyFilter::create('price', 'Price')->getValueFormOptions());
        $this->assertSame(['scale' => 4], MoneyFilter::create('price', 'Price')->withScale(4)->getValueFormOptions());
    }
}
