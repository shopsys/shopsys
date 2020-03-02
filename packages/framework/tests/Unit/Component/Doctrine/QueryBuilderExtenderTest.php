<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderExtender;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Tests\FrameworkBundle\Unit\Component\Doctrine\__fixtures\Product;

class QueryBuilderExtenderTest extends TestCase
{
    public function testAddFirstJoinToQueryBuilder(): void
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $queryBuilder = new QueryBuilder($entityManager);

        $entityNameResolver = new EntityNameResolver([]);
        $queryBuilderExtender = new QueryBuilderExtender($entityNameResolver);
        $queryBuilder->from(Category::class, 'c');
        $queryBuilderExtender->addOrExtendJoin($queryBuilder, BaseProduct::class, 'p', '1 = 1');

        $joinDqlPart = $queryBuilder->getDQLPart('join');
        $this->assertCount(1, reset($joinDqlPart));
    }

    /**
     * @dataProvider extendJoinWithExtendedEntityProvider
     * @param string $firstJoinedEntity
     * @param string $secondJoinedEntity
     * @param string $expectedJoinedEntity
     * @param array $extensionMap
     */
    public function testExtendJoinWithExtendedEntity(
        string $firstJoinedEntity,
        string $secondJoinedEntity,
        string $expectedJoinedEntity,
        array $extensionMap
    ): void {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $queryBuilder = new QueryBuilder($entityManager);

        $entityNameResolver = new EntityNameResolver($extensionMap);
        $queryBuilderExtender = new QueryBuilderExtender($entityNameResolver);
        $queryBuilder
            ->select('c')
            ->from(Category::class, 'c');
        $queryBuilderExtender->addOrExtendJoin($queryBuilder, $firstJoinedEntity, 'p', '0 = 0');
        $queryBuilderExtender->addOrExtendJoin($queryBuilder, $secondJoinedEntity, 'p', '1 = 1');

        $dql = $queryBuilder->getDQL();
        $this->assertSame('SELECT c FROM ' . Category::class . ' c INNER JOIN ' . $expectedJoinedEntity . ' p WITH 0 = 0 WHERE 1 = 1', $dql);
    }

    /**
     * @return array
     */
    public function extendJoinWithExtendedEntityProvider(): array
    {
        $extensionMap = [BaseProduct::class => Product::class];
        return [
            'extend base entity join with extended entity' => [
                'firstJoinedEntity' => BaseProduct::class,
                'secondJoinedEntity' => Product::class,
                'expectedJoinedEntity' => Product::class,
                'extensionMap' => $extensionMap,
            ],
            'extend extended entity join with base entity' => [
                'firstJoinedEntity' => Product::class,
                'secondJoinedEntity' => BaseProduct::class,
                'expectedJoinedEntity' => Product::class,
                'extensionMap' => $extensionMap,
            ],
        ];
    }
}
