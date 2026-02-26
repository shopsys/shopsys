<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\UnexpectedTypeException;

class NotNullableColumnsFinder
{
    /**
     * @param \Doctrine\Persistence\Mapping\ClassMetadata[] $classesMetadata
     * @return string[][]
     */
    public function getAllNotNullableColumnNamesIndexedByTableName(array $classesMetadata): array
    {
        $notNullableColumnNamesIndexedByTableName = [];

        foreach ($classesMetadata as $classMetadata) {
            if (!($classMetadata instanceof ClassMetadata)) {
                $message = 'Instance of ' . ClassMetadata::class . ' is required.';

                throw new UnexpectedTypeException($message);
            }
            $notNullableColumnNamesIndexedByTableName[$classMetadata->getTableName()] =
                array_merge(
                    $this->getNotNullableFieldColumnNames($classMetadata),
                    $this->getNotNullableAssociationColumnNames($classMetadata),
                );
        }

        return $notNullableColumnNamesIndexedByTableName;
    }

    /**
     * @return string[]
     */
    protected function getNotNullableFieldColumnNames(ClassMetadata $classMetadataInfo): array
    {
        $notNullableFieldNames = [];

        foreach ($classMetadataInfo->getFieldNames() as $fieldName) {
            if (!$classMetadataInfo->isNullable($fieldName)) {
                $notNullableFieldNames[] = $classMetadataInfo->getColumnName($fieldName);
            }
        }

        return $notNullableFieldNames;
    }

    /**
     * @return string[]
     */
    protected function getNotNullableAssociationColumnNames(ClassMetadata $classMetadataInfo): array
    {
        $notNullableAssociationNames = [];

        foreach ($classMetadataInfo->getAssociationMappings() as $associationMapping) {
            if (array_key_exists('joinColumns', $associationMapping) === false) {
                continue;
            }

            if ($associationMapping['joinColumns'][0]['nullable'] === false) {
                $notNullableAssociationNames[] = $associationMapping['joinColumns'][0]['name'];
            }
        }

        return $notNullableAssociationNames;
    }
}
