<?php

namespace Shopsys\FrameworkBundle\Component\Domain\Multidomain;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlQuoter;

class MultidomainEntityDataCreator
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Multidomain\MultidomainEntityClassFinderFacade
     */
    protected $multidomainEntityClassFinderFacade;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\SqlQuoter
     */
    protected $sqlQuoter;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Multidomain\MultidomainEntityClassFinderFacade $multidomainEntityClassFinderFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\SqlQuoter $sqlQuoter
     */
    public function __construct(
        MultidomainEntityClassFinderFacade $multidomainEntityClassFinderFacade,
        EntityManagerInterface $em,
        SqlQuoter $sqlQuoter
    ) {
        $this->multidomainEntityClassFinderFacade = $multidomainEntityClassFinderFacade;
        $this->em = $em;
        $this->sqlQuoter = $sqlQuoter;
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
                $columnNamesExcludingDomainId
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
            FROM ' . $quotedTableName . '
            WHERE domain_id = :templateDomainId',
            [
                'newDomainId' => $newDomainId,
                'templateDomainId' => $templateDomainId,
            ],
            [
                'newDomainId' => Types::INTEGER,
                'templateDomainId' => Types::INTEGER,
            ]
        );
    }
}
