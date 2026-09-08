<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Search\Filter;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Search\Filter\ChoiceFilter;
use Shopsys\AdministrationBundle\Component\Search\FilterRule;
use Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection;
use Shopsys\AdministrationBundle\Component\Search\Operator;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\ProxyQueryFactoryTrait;

final class ChoiceFilterTest extends TestCase
{
    use ProxyQueryFactoryTrait;

    public function testChoicesAreOfferedInTheValueSelect(): void
    {
        $filter = ChoiceFilter::create('type', 'Type', ['Basic' => 'basic', 'Inquiry' => 'inquiry']);

        $this->assertSame(ChoiceType::class, $filter->getValueFormType());
        $this->assertSame(['choices' => ['Basic' => 'basic', 'Inquiry' => 'inquiry']], $filter->getValueFormOptions());
        $this->assertSame([Operator::IS, Operator::IS_NOT], $filter->getAllowedOperators());
    }

    public function testMultipleIsRulesCombineIntoOneInConditionAndNotRulesIntoNotIn(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = ChoiceFilter::create('catnum', 'Catalog number', []);
        $filter->setProxyQuery($proxyQuery);
        $rules = new FilterRuleCollection([
            new FilterRule(Operator::IS, 'a', '0', 'advancedSearch_catnum'),
            new FilterRule(Operator::IS, 'b', 'new_0', 'advancedSearch_catnum'),
            new FilterRule(Operator::IS_NOT, 'c', 'new_1', 'advancedSearch_catnum'),
        ]);

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), $rules);

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertSame(
            'o.catnum IN (:advancedSearch_catnum_0_in) AND ((o.catnum NOT IN (:advancedSearch_catnum_0_notIn) OR o.catnum IS NULL))',
            (string)$queryBuilder->getDQLPart('where'),
        );
        $this->assertSame(['a', 'b'], $queryBuilder->getParameter('advancedSearch_catnum_0_in')->getValue());
        $this->assertSame(['c'], $queryBuilder->getParameter('advancedSearch_catnum_0_notIn')->getValue());
    }
}
