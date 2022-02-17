<?php

namespace Shopsys\FrameworkBundle\Component\EntityExtension;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
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

    /**
     * @var bool
     */
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

    /**
     * @var \Doctrine\ORM\Configuration
     */
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
                        $parentEntity
                    )
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
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $classMetadata
     */
    protected function setAssociationMappings(ClassMetadataInfo $classMetadata): void
    {
        $currentEntityClass = $classMetadata->getName();

        foreach ($this->parentEntitiesByClass[$currentEntityClass] as $parentClass) {
            $parentMetadata = $this->getClassMetadataForEntity($parentClass);
            foreach ($parentMetadata->getAssociationMappings() as $parentEntityClass => $parentEntityAssociationMapping) {
                if (isset($parentEntityAssociationMapping['sourceEntity']) && $parentEntityAssociationMapping['sourceEntity'] === $parentClass) {
                    $parentEntityAssociationMapping['sourceEntity'] = $currentEntityClass;
                }

                $parentEntityAssociationMapping['targetEntity'] = $this->ensureAbsoluteClassName(
                    $parentEntityAssociationMapping['targetEntity'],
                    $parentClass
                );

                $classMetadata->associationMappings[$parentEntityClass] = $parentEntityAssociationMapping;
            }
        }
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata
     */
    protected function setFieldMappings(ClassMetadataInfo $metadata): void
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
     * @return \Doctrine\ORM\Mapping\ClassMetadataInfo
     */
    protected function getClassMetadataForEntity(string $entityClass): ClassMetadataInfo
    {
        $classMetadata = new ClassMetadataInfo(
            $entityClass,
            $this->configuration->getNamingStrategy()
        );

        $metadataDriver = $this->configuration->getMetadataDriverImpl();

        if ($metadataDriver === null) {
            throw new LogicException('Metadata driver cannot be null');
        }

        $metadataDriver->loadMetadataForClass($entityClass, $classMetadata);

        return $classMetadata;
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $classMetadata
     */
    protected function updateAssociationMappingsToMappedSuperclasses(ClassMetadataInfo $classMetadata): void
    {
        foreach ($classMetadata->getAssociationMappings() as $name => $mapping) {
            if (!isset($mapping['targetEntity']) || !$this->isParentEntity($mapping['targetEntity'])) {
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
                $parentClass
            )
        );
    }
}
