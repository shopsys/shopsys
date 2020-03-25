<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Component\Doctrine;

use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderExtender;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\ShopBundle\Model\Category\Category;
use Shopsys\ShopBundle\Model\Product\Product;
use Tests\ShopBundle\Test\FunctionalTestCase;

class QueryBuilderExtenderTest extends FunctionalTestCase
{
    /**
     * @dataProvider extendJoinWithExtendedEntityProvider
     * @param string $firstJoinedEntity
     * @param string $secondJoinedEntity
     * @param string $expectedJoinedEntity
     */
    public function testExtendJoinWithExtendedEntity(
        string $firstJoinedEntity,
        string $secondJoinedEntity,
        string $expectedJoinedEntity
    ): void {
        /** @var \Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderExtender $queryBuilderExtender */
        $queryBuilderExtender = $this->getContainer()->get(QueryBuilderExtender::class);
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder
            ->select('c')
            ->from(Category::class, 'c')
            ->join($firstJoinedEntity, 'p', Join::WITH, '0 = 0');
        $queryBuilderExtender->addOrExtendJoin($queryBuilder, $secondJoinedEntity, 'p', '1 = 1');

        $dql = $queryBuilder->getDQL();
        $this->assertSame('SELECT c FROM ' . Category::class . ' c INNER JOIN ' . $expectedJoinedEntity . ' p WITH 0 = 0 WHERE 1 = 1', $dql);
    }

    /**
     * @return array
     */
    public function extendJoinWithExtendedEntityProvider(): array
    {
        return [
            'extend base entity join with extended entity' => [
                'firstJoinedEntity' => BaseProduct::class,
                'secondJoinedEntity' => Product::class,
                'expectedJoinedEntity' => Product::class,
            ],
            'extend extended entity join with base entity' => [
                'firstJoinedEntity' => Product::class,
                'secondJoinedEntity' => BaseProduct::class,
                'expectedJoinedEntity' => Product::class,
            ],
        ];
    }
}
