<?php

namespace Shopsys\FrameworkBundle\Component\EntityExtension;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class EntityExtensionParentMetadataCleanerEventSubscriber implements EventSubscriber
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver
     */
    private $entityNameResolver;

    public function __construct(EntityNameResolver $entityNameResolver)
    {
        $this->entityNameResolver = $entityNameResolver;
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $meta = $eventArgs->getClassMetadata();
        $entityName = $meta->getName();
        if ($this->entityNameResolver->resolve($entityName) !== $entityName) {
            $meta->isMappedSuperclass = true;
            $meta->identifier = [];
            $meta->generatorType = ClassMetadataInfo::GENERATOR_TYPE_NONE;
            $meta->fieldMappings = [];
            $meta->fieldNames = [];
            $meta->columnNames = [];
            $meta->associationMappings = [];
            $meta->idGenerator = new AssignedGenerator();
            $meta->embeddedClasses = [];
            $meta->inheritanceType = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
            $meta->discriminatorColumn = null;
            $meta->discriminatorMap = [];
            $meta->discriminatorValue = null;
        }
    }
}
