<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityExtension;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Override;

class EntityExtensionParentMetadataCleanerEventSubscriber implements EventSubscriber
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @return string[]
     */
    #[Override]
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $meta = $eventArgs->getClassMetadata();
        $entityName = $meta->getName();

        if (!$this->mustClean($entityName)) {
            return;
        }

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

    protected function mustClean(string $entityName): bool
    {
        return $this->isExtended($entityName) && !$this->isTranslation($entityName);
    }

    protected function isExtended(string $entityName): bool
    {
        return $this->entityNameResolver->resolve($entityName) !== $entityName;
    }

    protected function isTranslation(string $entityName): bool
    {
        return (bool)preg_match('~Translation$~', $entityName);
    }
}
