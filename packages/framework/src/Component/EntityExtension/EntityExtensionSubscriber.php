<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityExtension;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use LogicException;
use ReflectionClass;
use Webmozart\Assert\Assert;

/**
 * Inspired by joschi127/doctrine-entity-override-bundle (https://github.com/joschi127/doctrine-entity-override-bundle)
 */
class EntityExtensionSubscriber implements EventSubscriber
{
    /**
     * @var array<class-string, class-string>
     */
    protected array $entityExtensionMap;

    protected bool $isEntityMapLoaded = false;

    /**
     * @var array<class-string, array<class-string, class-string>>
     */
    protected array $parentEntitiesByClass = [];

    /**
     * @var array<class-string, class-string>
     */
    protected array $allParentEntities = [];

    /**
     * @var class-string[]|null
     */
    protected ?array $allRegisteredEntitiesCache = null;

    protected Configuration $configuration;

    /**
     * @param array<class-string, class-string> $entityExtensionMap
     */
    public function __construct(array $entityExtensionMap)
    {
        $this->setEntityExtensionMap($entityExtensionMap);
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata];
    }

    /**
     * @param array<class-string, class-string> $entityExtensionMap
     */
    protected function setEntityExtensionMap(array $entityExtensionMap): void
    {
        $this->entityExtensionMap = $entityExtensionMap;
        $this->isEntityMapLoaded = false;
    }

    protected function loadEntityExtensionMap(): void
    {
        $this->parentEntitiesByClass = [];
        $this->allParentEntities = [];
        $this->allRegisteredEntitiesCache = null;

        foreach ($this->entityExtensionMap as $parentEntity => $extendedEntity) {
            Assert::classExists($parentEntity);
            Assert::classExists($extendedEntity);

            $parentClasses = class_parents($extendedEntity);

            if (!array_key_exists($parentEntity, $parentClasses)) {
                throw new LogicException(
                    sprintf(
                        'Invalid entity extension mapping. Entity "%s" is not a child of "%s"',
                        $extendedEntity,
                        $parentEntity,
                    ),
                );
            }

            $parentClasses = array_filter($parentClasses, [$this, 'isOneOfRegisteredEntities']);

            $this->parentEntitiesByClass[$extendedEntity] = $parentClasses;
            $this->allParentEntities += $parentClasses;
        }

        $this->isEntityMapLoaded = true;
    }

    /**
     * @param \Doctrine\ORM\Event\LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $this->configuration = $eventArgs->getEntityManager()->getConfiguration();

        if (!$this->isEntityMapLoaded) {
            $this->loadEntityExtensionMap();
        }

        $currentClassMetadata = $eventArgs->getClassMetadata();

        if ($this->isExtendedEntity($currentClassMetadata->getName())) {
            $currentClassMetadata->isMappedSuperclass = false;
            $this->setAssociationMappings($currentClassMetadata);
            $this->setFieldMappings($currentClassMetadata);
        }

        if ($this->isParentEntity($currentClassMetadata->getName())) {
            $currentClassMetadata->isMappedSuperclass = true;
            $currentClassMetadata->associationMappings = [];
            $currentClassMetadata->fieldMappings = [];
            $currentClassMetadata->columnNames = [];
            $currentClassMetadata->fieldNames = [];
        }

        $this->updateAssociationMappingsToMappedSuperclasses($currentClassMetadata);
    }

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
                    $parentClass,
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
        string $overridingClassName,
    ): bool {
        foreach ($overridingClassProperties as $overridingClassProperty) {
            if ($overridingClassProperty->name === $parentClassPropertyName && $overridingClassProperty->class === $overridingClassName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadata $metadata
     */
    protected function setFieldMappings(ClassMetadata $metadata): void
    {
        $currentEntityClass = $metadata->getName();

        foreach ($this->parentEntitiesByClass[$currentEntityClass] as $parentClass) {
            $parentMetadata = $this->getClassMetadataForEntity($parentClass);

            foreach ($parentMetadata->reflFields as $name => $field) {
                if (!isset($metadata->reflFields[$name])) {
                    $metadata->reflFields[$name] = $field;
                }
            }
        }
    }

    /**
     * @param class-string $entityClass
     * @return bool
     */
    protected function isExtendedEntity(string $entityClass): bool
    {
        return array_key_exists($entityClass, $this->parentEntitiesByClass);
    }

    /**
     * @param class-string $entityClass
     * @return bool
     */
    protected function isParentEntity(string $entityClass): bool
    {
        return array_key_exists($entityClass, $this->allParentEntities);
    }

    /**
     * @param class-string $entityClass
     * @return bool
     */
    protected function isOneOfRegisteredEntities(string $entityClass): bool
    {
        if ($this->allRegisteredEntitiesCache === null) {
            $metadataDriver = $this->configuration->getMetadataDriverImpl();

            if ($metadataDriver === null) {
                throw new LogicException('Metadata driver cannot be null');
            }

            $this->allRegisteredEntitiesCache = $metadataDriver->getAllClassNames();
        }

        return in_array($entityClass, $this->allRegisteredEntitiesCache, true);
    }

    /**
     * @param class-string $entityClass
     * @return \Doctrine\ORM\Mapping\ClassMetadata
     */
    protected function getClassMetadataForEntity(string $entityClass): ClassMetadata
    {
        $classMetadata = new ClassMetadata(
            $entityClass,
            $this->configuration->getNamingStrategy(),
        );

        $metadataDriver = $this->configuration->getMetadataDriverImpl();

        if ($metadataDriver === null) {
            throw new LogicException('Metadata driver cannot be null');
        }

        $metadataDriver->loadMetadataForClass($entityClass, $classMetadata);

        return $classMetadata;
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadata $classMetadata
     */
    protected function updateAssociationMappingsToMappedSuperclasses(ClassMetadata $classMetadata): void
    {
        foreach ($classMetadata->getAssociationMappings() as $name => $mapping) {
            if (!$this->isParentEntity($mapping['targetEntity'])) {
                continue;
            }

            $overridingClass = $this->getOverridingClass($mapping['targetEntity']);

            $mapping['targetEntity'] = $overridingClass;
            $classMetadata->associationMappings[$name] = $mapping;
        }
    }

    /**
     * @param class-string $className
     * @return class-string|null
     */
    protected function getOverridingClass(string $className): ?string
    {
        if (array_key_exists($className, $this->entityExtensionMap)) {
            return $this->entityExtensionMap[$className];
        }

        foreach ($this->parentEntitiesByClass as $extendedClass => $parentClassesByClass) {
            foreach ($parentClassesByClass as $class) {
                if ($className === $class) {
                    return $extendedClass;
                }
            }
        }

        return null;
    }

    /**
     * @param string $classNameCandidate
     * @param class-string $parentClass
     * @return class-string
     */
    protected function ensureAbsoluteClassName(string $classNameCandidate, string $parentClass): string
    {
        if (class_exists($classNameCandidate) || str_contains($classNameCandidate, '\\')) {
            return $classNameCandidate;
        }

        $reflectionClass = new ReflectionClass($parentClass);
        $namespace = $reflectionClass->getNamespaceName();
        $absoluteTargetEntity = $namespace . '\\' . $classNameCandidate;

        if (class_exists($absoluteTargetEntity)) {
            return $absoluteTargetEntity;
        }

        throw new LogicException(
            sprintf(
                'Invalid entity mapping. Class "%s" set as target entity for "%s" (or any child class) not found.',
                $classNameCandidate,
                $parentClass,
            ),
        );
    }
}
