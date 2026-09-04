<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ProxyQueryTest extends TestCase
{
    use ProxyQueryFactoryTrait;

    public function testSelectOfEntityFieldNeedsNoJoin(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();

        $proxyQuery->addSelect('catnum');

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('SELECT o.catnum AS catnum', $queryBuilder->getDQL());
        $this->assertSame([], $queryBuilder->getDQLPart('join'));
    }

    public function testSelectOfAssociationJoinsAndSelectsTheJoinAlias(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();

        $proxyQuery->addSelect('brand');

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('SELECT brand_join AS brand', $queryBuilder->getDQL());
        $this->assertStringContainsString('brand_join WITH o.brand = brand_join.id', $queryBuilder->getDQL());
    }

    public function testSelectOfAssociationIdentifierSelectsIdentityWithoutJoin(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();

        $proxyQuery->addSelect('brand.id');

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('SELECT IDENTITY(o.brand) AS brand__id', $queryBuilder->getDQL());
        $this->assertSame([], $queryBuilder->getDQLPart('join'));
    }

    public function testSelectOfTranslatedFieldJoinsTranslationsWithLocale(): void
    {
        $proxyQuery = $this->createSearchProxyQuery('cs');

        $proxyQuery->addSelect('name');

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('SELECT o_tr.name AS name', $queryBuilder->getDQL());
        $this->assertStringContainsString(
            'LEFT JOIN o.translations o_tr WITH o_tr.locale = :o_tr_locale',
            $queryBuilder->getDQL(),
        );
        $this->assertSame('cs', $queryBuilder->getParameter('o_tr_locale')->getValue());
    }

    public function testTranslationJoinIsSharedBetweenDifferentTranslatedFields(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();

        $proxyQuery->addSelect('name');
        $proxyQuery->addSelect('description');

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('o_tr.name AS name', $queryBuilder->getDQL());
        $this->assertStringContainsString('o_tr.description AS description', $queryBuilder->getDQL());
        $joins = $queryBuilder->getDQLPart('join');
        $this->assertCount(1, $joins['o']);
    }

    public function testFieldExpressionForEntityFieldNeedsNoJoin(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();

        $expression = $proxyQuery->getFieldExpression('catnum');

        $this->assertSame('o.catnum', $expression);
        $this->assertSame([], $proxyQuery->getQueryBuilder()->getDQLPart('join'));
    }

    public function testFieldExpressionForManyToOneFieldJoinsAssociation(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();

        $expression = $proxyQuery->getFieldExpression('brand.name');

        $this->assertSame('brand_join.name', $expression);
        $this->assertStringContainsString(
            'brand_join WITH o.brand = brand_join.id',
            $proxyQuery->getQueryBuilder()->getDQL(),
        );
    }

    public function testFieldExpressionForTranslatedFieldJoinsTranslationsWithLocale(): void
    {
        $proxyQuery = $this->createSearchProxyQuery('cs');

        $expression = $proxyQuery->getFieldExpression('name');

        $this->assertSame('o_tr.name', $expression);
        $this->assertStringContainsString(
            'LEFT JOIN o.translations o_tr WITH o_tr.locale = :o_tr_locale',
            $proxyQuery->getQueryBuilder()->getDQL(),
        );
        $this->assertSame('cs', $proxyQuery->getQueryBuilder()->getParameter('o_tr_locale')->getValue());
    }

    public function testFieldExpressionForExplicitTranslationsPath(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();

        $expression = $proxyQuery->getFieldExpression('translations.name');

        $this->assertSame('translations_join.name', $expression);
        $this->assertStringContainsString(
            'LEFT JOIN o.translations translations_join WITH translations_join.locale = :translations_join_locale',
            $proxyQuery->getQueryBuilder()->getDQL(),
        );
    }

    public function testRepeatedFieldExpressionDoesNotDuplicateJoin(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();

        $proxyQuery->getFieldExpression('brand.name');
        $proxyQuery->getFieldExpression('brand.name');

        $joins = $proxyQuery->getQueryBuilder()->getDQLPart('join');
        $this->assertCount(1, $joins['o']);
    }

    public function testTranslationJoinIsSharedBetweenSelectAndFieldExpression(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();

        $proxyQuery->addSelect('name');
        $proxyQuery->getFieldExpression('name');

        $joins = $proxyQuery->getQueryBuilder()->getDQLPart('join');
        $this->assertCount(1, $joins['o']);
    }

    public function testFieldExpressionForAssociationIdentifierNeedsNoJoin(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();

        $expression = $proxyQuery->getFieldExpression('brand.id');

        $this->assertSame('IDENTITY(o.brand)', $expression);
        $this->assertSame([], $proxyQuery->getQueryBuilder()->getDQLPart('join'));
    }

    public function testFieldExpressionForAssociationThrowsException(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("ends with association 'brand'");

        $proxyQuery->getFieldExpression('brand');
    }

    public function testAssociationTargetExpressionNeedsNoJoin(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();

        $expression = $proxyQuery->getAssociationTargetExpression('brand');

        $this->assertSame('o.brand', $expression);
        $this->assertSame([], $proxyQuery->getQueryBuilder()->getDQLPart('join'));
    }

    public function testAssociationTargetExpressionForFieldThrowsException(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not end with an association');

        $proxyQuery->getAssociationTargetExpression('catnum');
    }

    public function testFieldExpressionForUnknownFieldThrowsException(): void
    {
        $proxyQuery = $this->createSearchProxyQuery();

        $this->expectException(InvalidArgumentException::class);

        $proxyQuery->getFieldExpression('nonexistent');
    }
}
