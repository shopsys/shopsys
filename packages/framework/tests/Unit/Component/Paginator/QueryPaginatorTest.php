<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Paginator;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\QueryBuilder;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Doctrine\MoneyType;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;
use Tests\FrameworkBundle\Unit\Component\Paginator\Fixture\PaginatedEntity;

class QueryPaginatorTest extends TestCase
{
    private const string MONEY_TYPE = 'money';

    #[Override]
    protected function setUp(): void
    {
        if (Type::hasType(self::MONEY_TYPE) === false) {
            Type::addType(self::MONEY_TYPE, MoneyType::class);
        }
    }

    /**
     * The count query is executed natively, so a value of a type of its own (money) is bound by its type there
     * as well — as it is, it cannot be bound at all.
     */
    public function testCountQueryBindsParametersByTheirTypes(): void
    {
        $price = Money::create(100);
        $queryBuilder = $this->createQueryBuilder()
            ->where('e.price > :price')
            ->andWhere('e.name = :name')
            ->setParameter('price', $price, self::MONEY_TYPE)
            ->setParameter('name', 'Hrnek');

        $parameters = $this->getTotalNativeQuery($queryBuilder)->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertSame($price, $parameters[0]->getValue());
        $this->assertSame(self::MONEY_TYPE, $parameters[0]->getType());
        $this->assertTrue($parameters[0]->typeWasSpecified());
        $this->assertSame('Hrnek', $parameters[1]->getValue());
        $this->assertFalse($parameters[1]->typeWasSpecified());
    }

    /**
     * The values follow the order of their placeholders in the query, a parameter used twice is bound twice.
     */
    public function testCountQueryBindsParametersInTheOrderOfTheQuery(): void
    {
        $queryBuilder = $this->createQueryBuilder()
            ->where('e.name = :name OR e.id IN (:ids)')
            ->andWhere('e.name <> :name')
            ->setParameter('ids', [1, 2], ArrayParameterType::INTEGER)
            ->setParameter('name', 'Hrnek');

        $parameters = $this->getTotalNativeQuery($queryBuilder)->getParameters();

        $this->assertSame(['Hrnek', [1, 2], 'Hrnek'], array_map(static fn ($parameter) => $parameter->getValue(), $parameters->toArray()));
        $this->assertSame(ArrayParameterType::INTEGER, $parameters[1]->getType());
    }

    private function getTotalNativeQuery(QueryBuilder $queryBuilder): NativeQuery
    {
        $queryPaginator = new class($queryBuilder) extends QueryPaginator {
            public function getTotalNativeQueryForTest(): NativeQuery
            {
                return $this->getTotalNativeQuery($this->queryBuilder);
            }
        };

        return $queryPaginator->getTotalNativeQueryForTest();
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $configuration = ORMSetup::createAttributeMetadataConfiguration([__DIR__ . '/Fixture'], true);

        // the server version is pinned so that the PostgreSQL platform is resolved without connecting to a database
        $connection = DriverManager::getConnection(
            [
                'driver' => 'pdo_pgsql',
                'serverVersion' => '16',
            ],
            $configuration,
        );

        return (new EntityManager($connection, $configuration))
            ->createQueryBuilder()
            ->select('e')
            ->from(PaginatedEntity::class, 'e');
    }
}
