<?php

namespace Shopsys\FrameworkBundle\Component\Domain\Multidomain;

use Doctrine\ORM\Mapping\ClassMetadata;

class MultidomainEntityClassFinder
{
    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadata[] $allClassesMetadata
     * @param string[] $ignoredEntitiesNames
     * @param string[] $manualMultidomainEntitiesNames
     * @return string[]
     */
    public function getMultidomainEntitiesNames(
        array $allClassesMetadata,
        array $ignoredEntitiesNames,
        array $manualMultidomainEntitiesNames
    ): array {
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

    private function isMultidomainEntity(ClassMetadata $classMetadata): bool
    {
        $identifierFieldNames = $classMetadata->getIdentifierFieldNames();

        return count($identifierFieldNames) > 1 && in_array('domainId', $identifierFieldNames, true);
    }
}
