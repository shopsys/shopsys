<?php

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\UnexpectedTypeException;

class StringColumnsFinder
{
    /**
     * @param \Doctrine\Persistence\Mapping\ClassMetadata[] $classesMetadata
     * @return array<string, string[]&mixed[]>
     */
    public function getAllStringColumnNamesIndexedByTableName(array $classesMetadata): array
    {
        $stringColumnNamesIndexedByTableName = [];
        foreach ($classesMetadata as $classMetadata) {
            if (!($classMetadata instanceof ClassMetadataInfo)) {
                $message = 'Instance of ' . ClassMetadataInfo::class . ' is required.';
                throw new UnexpectedTypeException($message);
            }
            $stringColumnNames = $this->getStringColumnNames($classMetadata);
            if (count($stringColumnNames) > 0) {
                $stringColumnNamesIndexedByTableName[$classMetadata->getTableName()] = $stringColumnNames;
            }
        }

        return $stringColumnNamesIndexedByTableName;
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $classMetadataInfo
     * @return string[]
     */
    protected function getStringColumnNames(ClassMetadataInfo $classMetadataInfo): array
    {
        $stringColumnNames = [];
        foreach ($classMetadataInfo->getFieldNames() as $fieldName) {
            if (in_array($classMetadataInfo->getTypeOfField($fieldName), $this->getDoctrineStringTypes(), true)) {
                $stringColumnNames[] = $classMetadataInfo->getColumnName($fieldName);
            }
        }

        return $stringColumnNames;
    }

    /**
     * @return string[]
     */
    protected function getDoctrineStringTypes(): array
    {
        return [
            'text',
            'string',
        ];
    }
}
