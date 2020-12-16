<?php

namespace Shopsys\FrameworkBundle\Component\Domain;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlQuoter;
use Shopsys\FrameworkBundle\Component\Doctrine\StringColumnsFinder;

class DomainUrlReplacer
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\StringColumnsFinder
     */
    protected $stringColumnsFinder;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\SqlQuoter
     */
    protected $sqlQuoter;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\StringColumnsFinder $stringColumnsFinder
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\SqlQuoter $sqlQuoter
     */
    public function __construct(
        StringColumnsFinder $stringColumnsFinder,
        EntityManagerInterface $em,
        SqlQuoter $sqlQuoter
    ) {
        $this->stringColumnsFinder = $stringColumnsFinder;
        $this->em = $em;
        $this->sqlQuoter = $sqlQuoter;
    }

    /**
     * @param string $domainConfigUrl
     * @param string $domainSettingUrl
     */
    public function replaceUrlInStringColumns($domainConfigUrl, $domainSettingUrl)
    {
        $stringColumnNames = $this->getAllStringColumnNamesIndexedByTableName();
        foreach ($stringColumnNames as $tableName => $columnNames) {
            $urlReplacementSql = $this->getUrlReplacementSql(
                $tableName,
                $columnNames,
                $domainSettingUrl,
                $domainConfigUrl
            );

            $this->em->getConnection()->executeStatement($urlReplacementSql);
        }
    }

    /**
     * @return string[][]
     */
    protected function getAllStringColumnNamesIndexedByTableName()
    {
        $classesMetadata = $this->em->getMetadataFactory()->getAllMetadata();

        return $this->stringColumnsFinder->getAllStringColumnNamesIndexedByTableName($classesMetadata);
    }

    /**
     * @param string $tableName
     * @param string[] $columnNames
     * @param string $domainSettingUrl
     * @param string $domainConfigUrl
     * @return string
     */
    protected function getUrlReplacementSql($tableName, array $columnNames, $domainSettingUrl, $domainConfigUrl)
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
