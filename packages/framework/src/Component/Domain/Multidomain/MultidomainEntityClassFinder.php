<?php

namespace Shopsys\FrameworkBundle\Component\Domain\Multidomain;

use Doctrine\Persistence\Mapping\ClassMetadata;

class MultidomainEntityClassFinder
{
    /**
     * @param \Doctrine\Persistence\Mapping\ClassMetadata[] $allClassesMetadata
     * @param string[] $ignoredEntitiesNames
     * @param string[] $manualMultidomainEntitiesNames
     * @return string[]
     */
    public function getMultidomainEntitiesNames(
        array $allClassesMetadata,
        array $ignoredEntitiesNames,
        array $manualMultidomainEntitiesNames
    ) {
        $multidomainEntitiesNames = [];
        foreach ($allClassesMetadata as $classMetadata) {
            $entityName = $classMetadata->getName();
            $isEntityIgnored = in_array($entityName, $ignoredEntitiesNames, true);
            $isManualMultidomainEntity = in_array($entityName, $manualMultidomainEntitiesNames, true);
            if ($isManualMultidomainEntity
                || !$isEntityIgnored && $this->isMultidomainEntity($classMetadata)
            ) {
                $multidomainEntitiesNames[] = $classMetadata->getName();
            }
        }

        return $multidomainEntitiesNames;
    }

    /**
     * @param \Doctrine\Persistence\Mapping\ClassMetadata $classMetadata
     * @return bool
     */
    protected function isMultidomainEntity(ClassMetadata $classMetadata)
    {
        $identifierFieldNames = $classMetadata->getIdentifierFieldNames();

        return count($identifierFieldNames) > 1 && in_array('domainId', $identifierFieldNames, true);
    }
}
