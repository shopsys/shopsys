<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Doctrine;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use Override;

final class DatagridHydrator extends AbstractHydrator
{
    public const HYDRATION_MODE = 'DatagridHydrator';

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function hydrateAllData(): array
    {
        $result = [];

        while ($data = $this->stmt->fetchAssociative()) {
            $this->hydrateRowData($data, $result);
        }

        return $result;
    }

    #[Override]
    protected function hydrateRowData(array $row, array &$result): void
    {
        $rowData = [];
        $associations = [];
        $nullableAssociations = [];

        foreach ($row as $key => $value) {
            $cacheKeyInfo = $this->hydrateColumnInfo($key);

            if ($cacheKeyInfo === null) {
                continue;
            }

            $transformedValue = $this->transformValue($value, $cacheKeyInfo);

            if (isset($cacheKeyInfo['dqlAlias'])) {
                $this->processAssociation(
                    $cacheKeyInfo,
                    $transformedValue,
                    $associations,
                    $nullableAssociations,
                );

                continue;
            }

            if ($cacheKeyInfo['isScalar'] === true) {
                $rowData[$this->transformFieldNameToDotNotation($cacheKeyInfo['fieldName'])] = $transformedValue;
            }
        }

        $this->finalizeRowData($associations, $nullableAssociations, $rowData);

        $result[] = $rowData;
    }

    private function finalizeRowData(array $associations, array $nullableAssociations, array &$rowData): void
    {
        foreach ($associations as $dqlAlias => $data) {
            $associationName = $this->transformFieldNameToDotNotation(
                $this->rsm->entityMappings[$dqlAlias],
            );

            $rowData[$associationName] = in_array($dqlAlias, $nullableAssociations, true)
                ? null
                : $this->uow->createEntity($this->rsm->aliasMap[$dqlAlias], $data, $this->hints);
        }
    }

    /**
     * Replace __ with . in $fieldname to allow dot notation access for datagrid
     */
    private function transformFieldNameToDotNotation(string $fieldName): string
    {
        return str_replace('__', '.', $fieldName);
    }

    private function transformValue(mixed $value, array $cacheKeyInfo): mixed
    {
        $type = $cacheKeyInfo['type'] ?? null;

        return $type ? $type->convertToPHPValue($value, $this->platform) : $value;
    }

    private function processAssociation(
        array $cacheKeyInfo,
        mixed $value,
        array &$associations,
        array &$nullableAssociations,
    ): void {
        $alias = $cacheKeyInfo['dqlAlias'];
        $fieldName = $cacheKeyInfo['fieldName'];

        if ($cacheKeyInfo['isIdentifier'] === true && $value === null) {
            $nullableAssociations[] = $alias;
        }

        $associations[$alias][$fieldName] = $value;
    }

    #[Override]
    protected function cleanup(): void
    {
        parent::cleanup();

        $this->uow->triggerEagerLoads();
        $this->uow->hydrationComplete();
    }
}
