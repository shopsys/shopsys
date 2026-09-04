<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Search\Filter;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Search\Filter\DateFilter;
use Shopsys\AdministrationBundle\Component\Search\FilterRule;
use Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection;
use Shopsys\AdministrationBundle\Component\Search\Operator;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\ProxyQueryFactoryTrait;

final class DateFilterTest extends TestCase
{
    use ProxyQueryFactoryTrait;

    public function testBeforeComparesWithMidnightOfTheSelectedDay(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = DateFilter::create('createdAt', 'Created at')->onFields('id');
        $filter->setProxyQuery($proxyQuery);
        $rule = new FilterRule(Operator::BEFORE, new DateTimeImmutable('2026-08-27 14:30:00'), '0', 'advancedSearch_createdAt');

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), new FilterRuleCollection([$rule]));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertSame('o.id < :advancedSearch_createdAt_0_value', (string)$queryBuilder->getDQLPart('where'));
        $this->assertSame(
            '2026-08-27 00:00:00',
            $queryBuilder->getParameter('advancedSearch_createdAt_0_value')->getValue()->format('Y-m-d H:i:s'),
        );
    }

    public function testAfterIncludesTheSelectedDay(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = DateFilter::create('createdAt', 'Created at')->onFields('id');
        $filter->setProxyQuery($proxyQuery);
        $rule = new FilterRule(Operator::AFTER, new DateTimeImmutable('2026-08-27'), '0', 'advancedSearch_createdAt');

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), new FilterRuleCollection([$rule]));

        $this->assertSame(
            'o.id >= :advancedSearch_createdAt_0_value',
            (string)$proxyQuery->getQueryBuilder()->getDQLPart('where'),
        );
    }

    public function testIsMatchesTheWholeDay(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = DateFilter::create('createdAt', 'Created at')->onFields('id');
        $filter->setProxyQuery($proxyQuery);
        $rule = new FilterRule(Operator::IS, new DateTimeImmutable('2026-08-27 14:30:00'), '0', 'advancedSearch_createdAt');

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), new FilterRuleCollection([$rule]));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertSame(
            'o.id >= :advancedSearch_createdAt_0_from AND o.id < :advancedSearch_createdAt_0_to',
            (string)$queryBuilder->getDQLPart('where'),
        );
        $this->assertSame(
            '2026-08-27 00:00:00',
            $queryBuilder->getParameter('advancedSearch_createdAt_0_from')->getValue()->format('Y-m-d H:i:s'),
        );
        $this->assertSame(
            '2026-08-28 00:00:00',
            $queryBuilder->getParameter('advancedSearch_createdAt_0_to')->getValue()->format('Y-m-d H:i:s'),
        );
    }

    public function testNonDateValueThrowsException(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();
        $filter = DateFilter::create('createdAt', 'Created at')->onFields('id');
        $filter->setProxyQuery($proxyQuery);
        $rule = new FilterRule(Operator::IS, 'not-a-date', '0', 'advancedSearch_createdAt');

        $this->expectException(InvalidArgumentException::class);

        $filter->extendQueryBuilder($proxyQuery->getQueryBuilder(), new FilterRuleCollection([$rule]));
    }
}
