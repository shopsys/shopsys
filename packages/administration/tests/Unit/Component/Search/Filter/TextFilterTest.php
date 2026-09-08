<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Search\Filter;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Search\Filter\TextFilter;
use Shopsys\AdministrationBundle\Component\Search\FilterRule;
use Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection;
use Shopsys\AdministrationBundle\Component\Search\Operator;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\ProxyQueryFactoryTrait;

final class TextFilterTest extends TestCase
{
    use ProxyQueryFactoryTrait;

    public function testFilterDefaults(): void
    {
        $filter = TextFilter::create('catnum', 'Catalog number');

        $this->assertSame('catnum', $filter->getName());
        $this->assertSame('Catalog number', $filter->getLabel());
        $this->assertSame(
            [Operator::CONTAINS, Operator::NOT_CONTAINS, Operator::IS, Operator::IS_NOT, Operator::NOT_SET],
            $filter->getAllowedOperators(),
        );
        $this->assertSame(TextType::class, $filter->getValueFormType());
    }

    public function testContainsMatchesAnyOfTheFields(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = TextFilter::create('search', 'Search')->onFields('catnum', 'brand.name');
        $filter->setProxyQuery($proxyQuery);
        $rule = new FilterRule(Operator::CONTAINS, 'foo*', '0', 'advancedSearch_search');

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), new FilterRuleCollection([$rule]));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertSame(
            '(NORMALIZED(o.catnum) LIKE NORMALIZED(:advancedSearch_search_0_value) OR NORMALIZED(brand_join.name) LIKE NORMALIZED(:advancedSearch_search_0_value))',
            (string)$queryBuilder->getDQLPart('where'),
        );
        $this->assertSame('%foo%%', $queryBuilder->getParameter('advancedSearch_search_0_value')->getValue());
    }

    public function testNotContainsRequiresAllFieldsAndMatchesNull(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = TextFilter::create('search', 'Search')->onFields('catnum', 'brand.name');
        $filter->setProxyQuery($proxyQuery);
        $rule = new FilterRule(Operator::NOT_CONTAINS, 'foo', '0', 'advancedSearch_search');

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), new FilterRuleCollection([$rule]));

        $this->assertSame(
            '((NORMALIZED(o.catnum) NOT LIKE NORMALIZED(:advancedSearch_search_0_value) OR o.catnum IS NULL)'
            . ' AND (NORMALIZED(brand_join.name) NOT LIKE NORMALIZED(:advancedSearch_search_0_value) OR brand_join.name IS NULL))',
            (string)$proxyQuery->getQueryBuilder()->getDQLPart('where'),
        );
    }

    public function testIsNotMatchesDifferentValueAndNull(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = TextFilter::create('catnum', 'Catalog number');
        $filter->setProxyQuery($proxyQuery);
        $rule = new FilterRule(Operator::IS_NOT, 'foo', '0', 'advancedSearch_catnum');

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), new FilterRuleCollection([$rule]));

        $this->assertSame(
            '((NORMALIZED(o.catnum) != NORMALIZED(:advancedSearch_catnum_0_value) OR o.catnum IS NULL))',
            (string)$proxyQuery->getQueryBuilder()->getDQLPart('where'),
        );
    }

    public function testIsMatchesExactValueWithoutWildcardTranslation(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = TextFilter::create('catnum', 'Catalog number');
        $filter->setProxyQuery($proxyQuery);
        $rule = new FilterRule(Operator::IS, 'foo*', '0', 'advancedSearch_catnum');

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), new FilterRuleCollection([$rule]));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertSame(
            '(NORMALIZED(o.catnum) = NORMALIZED(:advancedSearch_catnum_0_value))',
            (string)$queryBuilder->getDQLPart('where'),
        );
        $this->assertSame('foo*', $queryBuilder->getParameter('advancedSearch_catnum_0_value')->getValue());
    }

    public function testNotSetChecksNullWithoutParameter(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = TextFilter::create('catnum', 'Catalog number');
        $filter->setProxyQuery($proxyQuery);
        $rule = new FilterRule(Operator::NOT_SET, null, '0', 'advancedSearch_catnum');

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), new FilterRuleCollection([$rule]));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertSame('(o.catnum IS NULL)', (string)$queryBuilder->getDQLPart('where'));
        $this->assertCount(0, $queryBuilder->getParameters());
    }

    public function testExpressionReplacesFieldPaths(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = TextFilter::create('phone', 'Phone')
            ->onExpression("CONCAT(COALESCE(o.catnum, ''), COALESCE(o.id, ''))");
        $filter->setProxyQuery($proxyQuery);
        $rule = new FilterRule(Operator::CONTAINS, '123', '0', 'advancedSearch_phone');

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), new FilterRuleCollection([$rule]));

        $this->assertStringContainsString(
            "NORMALIZED(CONCAT(COALESCE(o.catnum, ''), COALESCE(o.id, ''))) LIKE",
            (string)$proxyQuery->getQueryBuilder()->getDQLPart('where'),
        );
    }

    public function testTranslatedFieldIsSearchedThroughTranslationJoin(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = TextFilter::create('name', 'Name');
        $filter->setProxyQuery($proxyQuery);
        $rule = new FilterRule(Operator::CONTAINS, 'foo', '0', 'advancedSearch_name');

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), new FilterRuleCollection([$rule]));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('NORMALIZED(o_tr.name) LIKE', (string)$queryBuilder->getDQLPart('where'));
        $this->assertStringContainsString('LEFT JOIN o.translations o_tr', $queryBuilder->getDQL());
    }
}
