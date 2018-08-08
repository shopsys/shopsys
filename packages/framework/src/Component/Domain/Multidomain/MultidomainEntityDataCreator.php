<?php

namespace Shopsys\FrameworkBundle\Component\Domain\Multidomain;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlQuoter;

class MultidomainEntityDataCreator
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Multidomain\MultidomainEntityClassFinderFacade
     */
    private $multidomainEntityClassFinderFacade;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\SqlQuoter
     */
    private $sqlQuoter;

    public function __construct(
        MultidomainEntityClassFinderFacade $multidomainEntityClassFinderFacade,
        EntityManagerInterface $em,
        SqlQuoter $sqlQuoter
    ) {
        $this->multidomainEntityClassFinderFacade = $multidomainEntityClassFinderFacade;
        $this->em = $em;
        $this->sqlQuoter = $sqlQuoter;
    }

    public function copyAllMultidomainDataForNewDomain(int $templateDomainId, int $newDomainId): void
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
                $columnNamesExcludingDomainId
            );
        }
    }

    /**
     * @param string[] $columnNames
     */
    private function copyMultidomainDataForNewDomain(int $templateDomainId, int $newDomainId, string $tableName, array $columnNames): void
    {
        $quotedColumnNames = $this->sqlQuoter->quoteIdentifiers($columnNames);
        $quotedColumnNamesSql = implode(', ', $quotedColumnNames);
        $quotedTableName = $this->sqlQuoter->quoteIdentifier($tableName);
        $query = $this->em->createNativeQuery(
            'INSERT INTO ' . $quotedTableName . ' (domain_id, ' . $quotedColumnNamesSql . ')
            SELECT :newDomainId, ' . $quotedColumnNamesSql . '
            FROM ' . $quotedTableName . '
            WHERE domain_id = :templateDomainId',
            new ResultSetMapping()
        );
        $query->execute([
            'newDomainId' => $newDomainId,
            'templateDomainId' => $templateDomainId,
        ]);
    }
}
