<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Search;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Search\Exception\QuickSearchNotConfiguredException;
use Shopsys\AdministrationBundle\Component\Search\QuickSearchApplier;
use Shopsys\AdministrationBundle\Component\Search\QuickSearchDefinition;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\ProxyQueryFactoryTrait;

final class QuickSearchApplierTest extends TestCase
{
    use ProxyQueryFactoryTrait;

    public function testGeneratedConditionSearchesAllFieldsWithEscapedWildcards(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $quickSearchDefinition = new QuickSearchDefinition(['catnum', 'brand.name'], null, null);

        $this->createQuickSearchApplier()->apply($quickSearchDefinition, $proxyQuery, 'foo*b_r');

        $whereDql = (string)$proxyQuery->getQueryBuilder()->getDQLPart('where');
        $this->assertStringContainsString('NORMALIZED(o.catnum) LIKE NORMALIZED(:crudQuickSearchText)', $whereDql);
        $this->assertStringContainsString('NORMALIZED(brand_join.name) LIKE NORMALIZED(:crudQuickSearchText)', $whereDql);
        $this->assertStringContainsString(' OR ', $whereDql);
        $this->assertSame(
            '%foo%b\_r%',
            $proxyQuery->getQueryBuilder()->getParameter('crudQuickSearchText')->getValue(),
        );
    }

    public function testQueryCallbackReplacesGeneratedCondition(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $quickSearchDefinition = new QuickSearchDefinition(['catnum'], null, null);
        $quickSearchDefinition->queryCallback(static function (QueryBuilder $queryBuilder, string $searchText): void {
            $queryBuilder->andWhere('o.catnum = :exactCatnum')->setParameter('exactCatnum', $searchText);
        });

        $this->createQuickSearchApplier()->apply($quickSearchDefinition, $proxyQuery, 'ABC-123');

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertSame('o.catnum = :exactCatnum', (string)$queryBuilder->getDQLPart('where'));
        $this->assertSame('ABC-123', $queryBuilder->getParameter('exactCatnum')->getValue());
        $this->assertNull($queryBuilder->getParameter('crudQuickSearchText'));
    }

    public function testApplyingWithoutFieldsAndCallbackThrowsException(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $quickSearchDefinition = new QuickSearchDefinition([], null, null);

        $this->expectException(QuickSearchNotConfiguredException::class);

        $this->createQuickSearchApplier()->apply($quickSearchDefinition, $proxyQuery, 'anything');
    }

    private function createQuickSearchApplier(): QuickSearchApplier
    {
        return new QuickSearchApplier(new DatabaseSearchingHelper());
    }
}
