<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Doctrine\ORM\EntityManagerInterface;

final class TableRowTestHelper
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param string[] $primaryKeyColumns
     * @return array<string, string>
     */
    public function getRowVersionsIndexedByPrimaryKey(string $tableName, array $primaryKeyColumns): array
    {
        return $this->getColumnValuesIndexedByPrimaryKey($tableName, $primaryKeyColumns, 'ctid::text');
    }

    /**
     * @param string[] $primaryKeyColumns
     * @return array<string, string>
     */
    public function getColumnValuesIndexedByPrimaryKey(
        string $tableName,
        array $primaryKeyColumns,
        string $columnExpression,
    ): array {
        return $this->em->getConnection()->fetchAllKeyValue(
            sprintf(
                'SELECT CONCAT_WS(\'-\', %s) AS primary_key, %s AS column_value FROM %s ORDER BY primary_key',
                implode(', ', $primaryKeyColumns),
                $columnExpression,
                $tableName,
            ),
        );
    }
}
