<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\UnexpectedTypeException;

class StringColumnsFinder
{
    /**
     * @param \Doctrine\Persistence\Mapping\ClassMetadata[] $classesMetadata
     * @return string[][]
     */
    public function getAllStringColumnNamesIndexedByTableName(array $classesMetadata)
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
    protected function getStringColumnNames(ClassMetadataInfo $classMetadataInfo)
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
    protected function getDoctrineStringTypes()
    {
        return [
            'text',
            'string',
        ];
    }
}
