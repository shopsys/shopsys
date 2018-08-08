<?php

namespace Shopsys\FrameworkBundle\Component\Domain;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlQuoter;
use Shopsys\FrameworkBundle\Component\Doctrine\StringColumnsFinder;

class DomainUrlService
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\StringColumnsFinder
     */
    private $stringColumnsFinder;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\SqlQuoter
     */
    private $sqlQuoter;

    public function __construct(
        StringColumnsFinder $stringColumnsFinder,
        EntityManagerInterface $em,
        SqlQuoter $sqlQuoter
    ) {
        $this->stringColumnsFinder = $stringColumnsFinder;
        $this->em = $em;
        $this->sqlQuoter = $sqlQuoter;
    }

    public function replaceUrlInStringColumns(string $domainConfigUrl, string $domainSettingUrl): void
    {
        $stringColumnNames = $this->getAllStringColumnNamesIndexedByTableName();
        foreach ($stringColumnNames as $tableName => $columnNames) {
            $urlReplacementSql = $this->getUrlReplacementSql($tableName, $columnNames, $domainSettingUrl, $domainConfigUrl);

            $this->em->createNativeQuery($urlReplacementSql, new ResultSetMapping())->execute();
        }
    }

    /**
     * @return string[][]
     */
    private function getAllStringColumnNamesIndexedByTableName()
    {
        $classesMetadata = $this->em->getMetadataFactory()->getAllMetadata();

        return $this->stringColumnsFinder->getAllStringColumnNamesIndexedByTableName($classesMetadata);
    }

    /**
     * @param string[] $columnNames
     */
    private function getUrlReplacementSql(string $tableName, array $columnNames, string $domainSettingUrl, string $domainConfigUrl): string
    {
        $sqlParts = [];
        $quotedTableName = $this->sqlQuoter->quoteIdentifier($tableName);
        $quotedColumnNames = $this->sqlQuoter->quoteIdentifiers($columnNames);
        $quotedDomainSettingUrl = $this->sqlQuoter->quote($domainSettingUrl);
        $quotedDomainConfigUrl = $this->sqlQuoter->quote($domainConfigUrl);
        foreach ($quotedColumnNames as $quotedName) {
            $sqlParts[] =
                $quotedName . ' = replace(' . $quotedName . ', ' . $quotedDomainSettingUrl . ', ' . $quotedDomainConfigUrl . ')';
        }

        return 'UPDATE ' . $quotedTableName . ' SET ' . implode(',', $sqlParts);
    }
}
