<?php

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

class NotNullableColumnsFinder
{
    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadata[] $classesMetadata
     * @return string[][]
     */
    public function getAllNotNullableColumnNamesIndexedByTableName(array $classesMetadata)
    {
        $notNullableColumnNamesIndexedByTableName = [];
        foreach ($classesMetadata as $classMetadata) {
            if (!($classMetadata instanceof ClassMetadataInfo)) {
                $message = 'Instance of ' . ClassMetadataInfo::class . ' is required.';
                throw new \Shopsys\FrameworkBundle\Component\Doctrine\Exception\UnexpectedTypeException($message);
            }
            $notNullableColumnNamesIndexedByTableName[$classMetadata->getTableName()] =
                array_merge(
                    $this->getNotNullableFieldColumnNames($classMetadata),
                    $this->getNotNullableAssociationColumnNames($classMetadata)
                );
        }

        return $notNullableColumnNamesIndexedByTableName;
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $classMetadataInfo
     * @return string[]
     */
    protected function getNotNullableFieldColumnNames(ClassMetadataInfo $classMetadataInfo)
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
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $classMetadataInfo
     * @return string[]
     */
    protected function getNotNullableAssociationColumnNames(ClassMetadataInfo $classMetadataInfo)
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
