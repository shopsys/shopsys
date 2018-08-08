<?php

namespace Shopsys\FrameworkBundle\Component\Translation;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Prezent\Doctrine\Translatable\TranslationInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\NotNullableColumnsFinder;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlQuoter;

class TranslatableEntityDataCreator
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\NotNullableColumnsFinder
     */
    private $notNullableColumnsFinder;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\SqlQuoter
     */
    private $sqlQuoter;

    public function __construct(
        EntityManagerInterface $em,
        NotNullableColumnsFinder $notNullableColumnsFinder,
        SqlQuoter $sqlQuoter
    ) {
        $this->em = $em;
        $this->notNullableColumnsFinder = $notNullableColumnsFinder;
        $this->sqlQuoter = $sqlQuoter;
    }
    
    public function copyAllTranslatableDataForNewLocale(string $templateLocale, string $newLocale): void
    {
        $notNullableColumns = $this->notNullableColumnsFinder->getAllNotNullableColumnNamesIndexedByTableName(
            $this->getAllTranslatableEntitiesMetadata()
        );
        foreach ($notNullableColumns as $tableName => $columnNames) {
            $columnNamesExcludingIdAndLocale = array_filter($columnNames, function ($columnName) {
                return $columnName !== 'id' && $columnName !== 'locale';
            });

            $this->copyTranslatableDataForNewLocale($templateLocale, $newLocale, $tableName, $columnNamesExcludingIdAndLocale);
        }
    }

    /**
     * @return \Doctrine\ORM\Mapping\ClassMetadata[]
     */
    private function getAllTranslatableEntitiesMetadata(): array
    {
        $translatableEntitiesMetadata = [];
        $allClassesMetadata = $this->em->getMetadataFactory()->getAllMetadata();
        foreach ($allClassesMetadata as $classMetadata) {
            /* @var $classMetadata \Doctrine\ORM\Mapping\ClassMetadata */
            if (is_subclass_of($classMetadata->name, TranslationInterface::class)) {
                $translatableEntitiesMetadata[] = $classMetadata;
            }
        }

        return $translatableEntitiesMetadata;
    }

    /**
     * @param string[] $columnNames
     */
    private function copyTranslatableDataForNewLocale(string $templateLocale, string $newLocale, string $tableName, array $columnNames): void
    {
        $quotedColumnNames = $this->sqlQuoter->quoteIdentifiers($columnNames);
        $quotedColumnNamesSql = implode(', ', $quotedColumnNames);
        $quotedTableName = $this->sqlQuoter->quoteIdentifier($tableName);
        $query = $this->em->createNativeQuery(
            'INSERT INTO ' . $quotedTableName . ' (locale, ' . $quotedColumnNamesSql . ')
            SELECT :newLocale, ' . $quotedColumnNamesSql . '
            FROM ' . $quotedTableName . '
            WHERE locale = :templateLocale
            ON CONFLICT DO NOTHING',
            new ResultSetMapping()
        );
        $query->execute([
            'newLocale' => $newLocale,
            'templateLocale' => $templateLocale,
        ]);
    }
}
