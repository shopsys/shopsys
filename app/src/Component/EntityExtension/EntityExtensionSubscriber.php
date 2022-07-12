<?php

declare(strict_types=1);

namespace App\Component\EntityExtension;

use Doctrine\ORM\Mapping\ClassMetadata;
use ReflectionClass;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityExtensionSubscriber as BaseEntityExtensionSubscriber;

/**
 * @see https://github.com/shopsys/shopsys/pull/2473
 */
class EntityExtensionSubscriber extends BaseEntityExtensionSubscriber
{
    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadata $classMetadata
     */
    protected function setAssociationMappings(ClassMetadata $classMetadata): void
    {
        $currentEntityClass = $classMetadata->getName();

        foreach ($this->parentEntitiesByClass[$currentEntityClass] as $parentClass) {
            $parentMetadata = $this->getClassMetadataForEntity($parentClass);
            foreach ($parentMetadata->getAssociationMappings() as $associationName => $parentEntityAssociationMapping) {
                if (isset($parentEntityAssociationMapping['sourceEntity']) && $parentEntityAssociationMapping['sourceEntity'] === $parentClass) {
                    $parentEntityAssociationMapping['sourceEntity'] = $currentEntityClass;
                }

                $parentEntityAssociationMapping['targetEntity'] = $this->ensureAbsoluteClassName(
                    $parentEntityAssociationMapping['targetEntity'],
                    $parentClass
                );

                $isDifferenceBetweenChildAssociationMappingAndParentAssociationMapping = !isset($classMetadata->associationMappings[$associationName]) || $classMetadata->associationMappings[$associationName] !== $parentEntityAssociationMapping;
                $isOverriddenPropertyInChildClass = true;
                if ($isDifferenceBetweenChildAssociationMappingAndParentAssociationMapping) {
                    $overridingClassReflection = new ReflectionClass($currentEntityClass);
                    $overridingClassProperties = $overridingClassReflection->getProperties();
                    $isOverriddenPropertyInChildClass = $this->checkIsOverriddenPropertyInChildClass($overridingClassProperties, $associationName, $currentEntityClass);
                }

                if (!$isDifferenceBetweenChildAssociationMappingAndParentAssociationMapping || !$isOverriddenPropertyInChildClass) {
                    $classMetadata->associationMappings[$associationName] = $parentEntityAssociationMapping;
                }
            }
        }
    }

    /**
     * @param \ReflectionProperty[] $overridingClassProperties
     * @param string $parentClassPropertyName
     * @param string $overridingClassName
     * @return bool
     */
    protected function checkIsOverriddenPropertyInChildClass(
        array $overridingClassProperties,
        string $parentClassPropertyName,
        string $overridingClassName
    ): bool {
        foreach ($overridingClassProperties as $overridingClassProperty) {
            if ($overridingClassProperty->name === $parentClassPropertyName && $overridingClassProperty->class === $overridingClassName) {
                return true;
            }
        }
        return false;
    }
}
