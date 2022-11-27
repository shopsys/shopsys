<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Doctrine;

use App\Model\Category\Category;
use App\Model\Product\Product;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderExtender;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Tests\App\Test\TransactionFunctionalTestCase;

class QueryBuilderExtenderTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderExtender
     * @inject
     */
    private QueryBuilderExtender $queryBuilderExtender;

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
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('c')
            ->from(Category::class, 'c')
            ->join($firstJoinedEntity, 'p', Join::WITH, '0 = 0');
        $this->queryBuilderExtender->addOrExtendJoin($queryBuilder, $secondJoinedEntity, 'p', '1 = 1');

        $dql = $queryBuilder->getDQL();
        $this->assertSame(
            'SELECT c FROM ' . Category::class . ' c INNER JOIN ' . $expectedJoinedEntity . ' p WITH 0 = 0 WHERE 1 = 1',
            $dql
        );
    }

    /**
     * @return array<string, array<string, class-string>>
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
