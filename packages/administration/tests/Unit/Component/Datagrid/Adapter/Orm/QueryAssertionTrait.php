<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm;

use Doctrine\ORM\QueryBuilder;

trait QueryAssertionTrait
{
    /**
     * Converting the query to SQL parses the DQL against the mapping without a database.
     */
    private function assertQuery(string $expectedDql, QueryBuilder $queryBuilder): void
    {
        $this->assertSame($expectedDql, $queryBuilder->getDQL());
        $queryBuilder->getQuery()->getSQL();
    }
}
