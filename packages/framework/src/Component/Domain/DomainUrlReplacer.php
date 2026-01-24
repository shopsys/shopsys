<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlQuoter;
use Shopsys\FrameworkBundle\Component\Doctrine\StringColumnsFinder;

class DomainUrlReplacer
{
    public function __construct(
        protected readonly StringColumnsFinder $stringColumnsFinder,
        protected readonly EntityManagerInterface $em,
        protected readonly SqlQuoter $sqlQuoter,
    ) {
    }

    public function replaceUrlInStringColumns(string $domainConfigUrl, string $domainSettingUrl): void
    {
        $stringColumnNames = $this->getAllStringColumnNamesIndexedByTableName();

        foreach ($stringColumnNames as $tableName => $columnNames) {
            $urlReplacementSql = $this->getUrlReplacementSql(
                $tableName,
                $columnNames,
                $domainSettingUrl,
                $domainConfigUrl,
            );

            $this->em->getConnection()->executeStatement($urlReplacementSql);
        }
    }

    /**
     * @return string[][]
     */
    protected function getAllStringColumnNamesIndexedByTableName(): array
    {
        $classesMetadata = $this->em->getMetadataFactory()->getAllMetadata();

        return $this->stringColumnsFinder->getAllStringColumnNamesIndexedByTableName($classesMetadata);
    }

    /**
     * @param string[] $columnNames
     */
    protected function getUrlReplacementSql(
        string $tableName,
        array $columnNames,
        string $domainSettingUrl,
        string $domainConfigUrl,
    ): string {
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
