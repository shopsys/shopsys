<?php

declare(strict_types=1);

namespace App\Component\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Shopsys\FrameworkBundle\Component\Doctrine\NotNullableColumnsFinder as BaseNotNullableColumnsFinder;

class NotNullableColumnsFinder extends BaseNotNullableColumnsFinder
{
    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $classMetadataInfo
     * @return string[]
     */
    protected function getNotNullableAssociationColumnNames(ClassMetadataInfo $classMetadataInfo): array
    {
        $notNullableAssociationNames = [];

        foreach ($classMetadataInfo->getAssociationMappings() as $associationMapping) {
            if (array_key_exists('joinColumns', $associationMapping)
                && $associationMapping['joinColumns'][0]['nullable'] === false
            ) {
                $notNullableAssociationNames[] = $associationMapping['joinColumns'][0]['name'];
            }
        }

        return $notNullableAssociationNames;
    }
}
