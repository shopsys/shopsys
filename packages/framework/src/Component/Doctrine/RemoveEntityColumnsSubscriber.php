<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Shopsys\FrameworkBundle\Component\Doctrine\Annotation\RemoveColumns;

class RemoveEntityColumnsSubscriber implements EventSubscriber
{
    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    protected Reader $annotationReader;

    /**
     * @param \Doctrine\Common\Annotations\Reader $annotationReader
     */
    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    /**
     * @param \Doctrine\ORM\Event\LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();

        /** @var \Shopsys\FrameworkBundle\Component\Doctrine\Annotation\RemoveColumns|null $annotation */
        $annotation = $this->annotationReader->getClassAnnotation($classMetadata->getReflectionClass(), RemoveColumns::class);
        if ($annotation !== null) {
            $this->removeColumnsFromEntityMappings($annotation->propertyNames, $classMetadata, $classMetadata->getName());
        }
    }

    /**
     * @param string[] $propertyNames
     * @param \Doctrine\ORM\Mapping\ClassMetadata $classMetadata
     * @param string $className
     */
    protected function removeColumnsFromEntityMappings(array $propertyNames, ClassMetadata $classMetadata, string $className): void
    {
        foreach ($propertyNames as $propertyName) {
            $classMetadata->associationMappings = $this->removeMappingByKey($propertyName, $classMetadata->associationMappings, $className);
            $classMetadata->fieldMappings = $this->removeMappingByKey($propertyName, $classMetadata->fieldMappings, $className);
            $classMetadata->columnNames = $this->removeMappingByKey($propertyName, $classMetadata->columnNames, $className);

            $classMetadata->fieldNames = $this->removeMappingByValue($propertyName, $classMetadata->fieldNames, $className);
        }
    }

    /**
     * @param string $propertyName
     * @param string[] $mapping
     * @param string $className
     * @return string[]
     */
    protected function removeMappingByKey(string $propertyName, array $mapping, string $className): array
    {
        $existsProperty = array_key_exists($propertyName, $mapping);

        if ($existsProperty) {
            unset($mapping[$propertyName]);
        }

        return $mapping;
    }

    /**
     * @param string $propertyName
     * @param string[] $mapping
     * @param string $className
     * @return string[]
     */
    protected function removeMappingByValue(string $propertyName, array $mapping, string $className): array
    {
        $key = array_search($propertyName, $mapping, true);

        if ($key !== false) {
            unset($mapping[$key]);
        }

        return $mapping;
    }
}
