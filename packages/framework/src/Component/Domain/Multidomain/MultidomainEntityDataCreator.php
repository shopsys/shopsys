<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain\Multidomain;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlQuoter;

class MultidomainEntityDataCreator
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Multidomain\MultidomainEntityClassFinderFacade $multidomainEntityClassFinderFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\SqlQuoter $sqlQuoter
     */
    public function __construct(
        protected readonly MultidomainEntityClassFinderFacade $multidomainEntityClassFinderFacade,
        protected readonly EntityManagerInterface $em,
        protected readonly SqlQuoter $sqlQuoter,
    ) {
    }

    /**
     * @param int $templateDomainId
     * @param int $newDomainId
     */
    public function copyAllMultidomainDataForNewDomain($templateDomainId, $newDomainId)
    {
        $columnNamesIndexedByTableName = $this->multidomainEntityClassFinderFacade
            ->getAllNotNullableColumnNamesIndexedByTableName();

        foreach ($columnNamesIndexedByTableName as $tableName => $columnNames) {
            $columnNamesExcludingDomainId = array_filter($columnNames, function ($columnName) {
                return $columnName !== 'id' && $columnName !== 'domain_id';
            });

            $this->copyMultidomainDataForNewDomain(
                $templateDomainId,
                $newDomainId,
                $tableName,
                $columnNamesExcludingDomainId,
            );
        }
    }

    /**
     * @param int $templateDomainId
     * @param int $newDomainId
     * @param string $tableName
     * @param string[] $columnNames
     */
    protected function copyMultidomainDataForNewDomain($templateDomainId, $newDomainId, $tableName, array $columnNames)
    {
        $quotedColumnNames = $this->sqlQuoter->quoteIdentifiers($columnNames);
        $quotedColumnNamesSql = implode(', ', $quotedColumnNames);
        $quotedTableName = $this->sqlQuoter->quoteIdentifier($tableName);

        $this->em->getConnection()->executeStatement(
            'INSERT INTO ' . $quotedTableName . ' (domain_id, ' . $quotedColumnNamesSql . ')
            SELECT :newDomainId, ' . $quotedColumnNamesSql . '
            FROM ' . $quotedTableName . ' qt
            WHERE domain_id = :templateDomainId
            ON CONFLICT DO NOTHING',
            [
                'newDomainId' => $newDomainId,
                'templateDomainId' => $templateDomainId,
            ],
            [
                'newDomainId' => Types::INTEGER,
                'templateDomainId' => Types::INTEGER,
            ],
        );
    }
}
