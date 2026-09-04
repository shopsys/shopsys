<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Search\Filter;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Search\Filter\BooleanFilter;
use Shopsys\AdministrationBundle\Component\Search\FilterRule;
use Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection;
use Shopsys\AdministrationBundle\Component\Search\Operator;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\ProxyQueryFactoryTrait;

final class BooleanFilterTest extends TestCase
{
    use ProxyQueryFactoryTrait;

    public function testIsMatchesTrueWithoutParameter(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = BooleanFilter::create('deleted', 'Deleted')->onFields('id');
        $filter->setProxyQuery($proxyQuery);
        $rule = new FilterRule(Operator::IS, '1', '0', 'advancedSearch_deleted');

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), new FilterRuleCollection([$rule]));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertSame('o.id = true', (string)$queryBuilder->getDQLPart('where'));
        $this->assertCount(0, $queryBuilder->getParameters());
    }

    public function testIsNotMatchesNotTrueAndNull(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = BooleanFilter::create('deleted', 'Deleted')->onFields('id');
        $filter->setProxyQuery($proxyQuery);
        $rule = new FilterRule(Operator::IS_NOT, '1', '0', 'advancedSearch_deleted');

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), new FilterRuleCollection([$rule]));

        $this->assertSame('(o.id != true OR o.id IS NULL)', (string)$proxyQuery->getQueryBuilder()->getDQLPart('where'));
    }

    public function testHiddenValueSubmitsFixedValueSoTheRuleIsNotSkipped(): void
    {
        $filter = BooleanFilter::create('deleted', 'Deleted');

        $this->assertSame(HiddenType::class, $filter->getValueFormType());
        $this->assertSame(['empty_data' => '1'], $filter->getValueFormOptions());
    }
}
