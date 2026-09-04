<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Search\Filter;

use LogicException;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Search\Filter\EntityFilter;
use Shopsys\AdministrationBundle\Component\Search\FilterRule;
use Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection;
use Shopsys\AdministrationBundle\Component\Search\Operator;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\ProxyQueryFactoryTrait;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\TestSearchBrand;

final class EntityFilterTest extends TestCase
{
    use ProxyQueryFactoryTrait;

    public function testValueSelectOffersTheRelatedEntities(): void
    {
        $filter = EntityFilter::create('brand', 'Brand', TestSearchBrand::class)->choiceLabel('name');

        $this->assertSame(EntityType::class, $filter->getValueFormType());
        $this->assertSame(['class' => TestSearchBrand::class, 'choice_label' => 'name'], $filter->getValueFormOptions());
    }

    public function testEntitiesAreComparedAgainstTheAssociationWithoutJoin(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = EntityFilter::create('brand', 'Brand', TestSearchBrand::class);
        $filter->setProxyQuery($proxyQuery);
        $firstBrand = new TestSearchBrand();
        $secondBrand = new TestSearchBrand();
        $rules = new FilterRuleCollection([
            new FilterRule(Operator::IS, $firstBrand, '0', 'advancedSearch_brand'),
            new FilterRule(Operator::IS, $secondBrand, 'new_0', 'advancedSearch_brand'),
        ]);

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), $rules);

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertSame('o.brand IN (:advancedSearch_brand_0_in)', (string)$queryBuilder->getDQLPart('where'));
        $this->assertSame([$firstBrand, $secondBrand], $queryBuilder->getParameter('advancedSearch_brand_0_in')->getValue());
        $this->assertSame([], $queryBuilder->getDQLPart('join'));
    }

    public function testIsNotExcludesTheEntitiesAndMatchesNull(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = EntityFilter::create('brand', 'Brand', TestSearchBrand::class);
        $filter->setProxyQuery($proxyQuery);
        $brand = new TestSearchBrand();
        $rules = new FilterRuleCollection([
            new FilterRule(Operator::IS_NOT, $brand, '0', 'advancedSearch_brand'),
        ]);

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), $rules);

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertSame(
            '(o.brand NOT IN (:advancedSearch_brand_0_notIn) OR o.brand IS NULL)',
            (string)$queryBuilder->getDQLPart('where'),
        );
        $this->assertSame([$brand], $queryBuilder->getParameter('advancedSearch_brand_0_notIn')->getValue());
    }

    public function testExpressionIsNotSupported(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = EntityFilter::create('brand', 'Brand', TestSearchBrand::class)->onExpression('IDENTITY(o.brand)');
        $filter->setProxyQuery($proxyQuery);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Filter "brand" supports exactly one searched association field.');

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), new FilterRuleCollection([]));
    }

    public function testMultipleFieldsAreNotSupported(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = EntityFilter::create('brand', 'Brand', TestSearchBrand::class)->onFields('brand', 'translations');
        $filter->setProxyQuery($proxyQuery);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Filter "brand" supports exactly one searched association field.');

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), new FilterRuleCollection([]));
    }
}
