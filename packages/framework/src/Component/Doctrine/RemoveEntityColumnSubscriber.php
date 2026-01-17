<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Override;
use Shopsys\FrameworkBundle\Component\Doctrine\Attribute\RemoveColumn;

class RemoveEntityColumnSubscriber implements EventSubscriber
{
    /**
     * @return string[]
     */
    #[Override]
    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();

        foreach ($classMetadata->getReflectionClass()->getProperties() as $property) {
            $reflectionAttributes = $property->getAttributes(RemoveColumn::class);

            if (count($reflectionAttributes) > 0) {
                $this->removeColumnFromEntityMappings($property->getName(), $classMetadata);
            }
        }
    }

    protected function removeColumnFromEntityMappings(string $propertyName, ClassMetadata $classMetadata): void
    {
        $classMetadata->associationMappings = $this->removeMappingByKey($propertyName, $classMetadata->associationMappings);
        $classMetadata->fieldMappings = $this->removeMappingByKey($propertyName, $classMetadata->fieldMappings);
        $classMetadata->columnNames = $this->removeMappingByKey($propertyName, $classMetadata->columnNames);
        $classMetadata->fieldNames = $this->removeMappingByValue($propertyName, $classMetadata->fieldNames);
    }

    protected function removeMappingByKey(string $propertyName, array $mapping): array
    {
        if (array_key_exists($propertyName, $mapping)) {
            unset($mapping[$propertyName]);
        }

        return $mapping;
    }

    protected function removeMappingByValue(string $propertyName, array $mapping): array
    {
        $key = array_search($propertyName, $mapping, true);

        if ($key !== false) {
            unset($mapping[$key]);
        }

        return $mapping;
    }
}
