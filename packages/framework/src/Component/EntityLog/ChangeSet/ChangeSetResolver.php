<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet;

use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfigFactory;
use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver\DataTypeResolverInterface;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade;
use Webmozart\Assert\Assert;

class ChangeSetResolver
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver\DataTypeResolverInterface[]
     */
    protected array $dataTypeResolvers;

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver\DataTypeResolverInterface[] $dataTypeResolvers
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfigFactory $loggableEntityConfigFactory
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade $entityLogFacade
     */
    public function __construct(
        iterable $dataTypeResolvers,
        protected readonly LoggableEntityConfigFactory $loggableEntityConfigFactory,
        protected readonly EntityLogFacade $entityLogFacade,
    ) {
        $this->registerDataTypeResolvers($dataTypeResolvers);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver\DataTypeResolverInterface[] $dataTypeResolvers
     */
    protected function registerDataTypeResolvers(iterable $dataTypeResolvers): void
    {
        Assert::allIsInstanceOf($dataTypeResolvers, DataTypeResolverInterface::class);

        $dataTypeResolversArray = [];

        foreach ($dataTypeResolvers as $dataTypeResolver) {
            $dataTypeResolversArray[] = $dataTypeResolver;
        }

        //sort data type resolvers from higher to lower priority
        usort($dataTypeResolversArray, function (DataTypeResolverInterface $a, DataTypeResolverInterface $b) {
            return $b->getPriority() - $a->getPriority();
        });

        $this->dataTypeResolvers = $dataTypeResolversArray;
    }

    /**
     * @param array $changeSet
     * @param object $entity
     * @return array
     */
    public function resolveChangeSetForEntity(array $changeSet, object $entity): array
    {
        $loggableSetup = $this->loggableEntityConfigFactory->getLoggableSetupByEntity($entity);

        $resolvedChangeSet = [];

        foreach ($changeSet as $property => $changes) {
            if (!$loggableSetup->isPropertyLoggable($property)) {
                continue;
            }
            $resolvedChanges = $this->getResolvedChanges($changes);

            if ($resolvedChanges) {
                $resolvedChangeSet[$property] = $resolvedChanges;
            }
        }

        return $resolvedChangeSet;
    }

    /**
     * @param array $changes
     * @return \Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges|null
     */
    protected function getResolvedChanges(array $changes): ?ResolvedChanges
    {
        foreach ($this->dataTypeResolvers as $dataTypeResolver) {
            if ($dataTypeResolver->isResolvedDataTypeByChanges($changes)) {
                $resolvedChanges = $dataTypeResolver->getResolvedChanges($changes);

                if ($resolvedChanges->isOldValueSameAsNewValue()) {
                    return null;
                }

                return $resolvedChanges;
            }
        }

        return null;
    }

    /**
     * @param \Doctrine\ORM\PersistentCollection[] $scheduledCollections
     * @param object $entity
     * @return array
     */
    public function resolveChangesOnCollectionForEntity(array $scheduledCollections, object $entity): array
    {
        $resolvedChangedCollections = [];

        foreach ($scheduledCollections as $scheduledCollection) {
            if ($scheduledCollection->getOwner() !== $entity) {
                continue;
            }

            $collectionChanges = new CollectionChanges();

            foreach ($scheduledCollection->getInsertDiff() as $insertEntity) {
                $loggableSetup = $this->loggableEntityConfigFactory->getLoggableSetupByEntity($insertEntity);
                $newReadableValue = $this->entityLogFacade->getEntityReadableIdentifierByEntityAndLoggableSetup(
                    $insertEntity,
                    $loggableSetup,
                );

                $collectionChanges->insertedItems[] = (new ResolvedChanges(
                    EntityLogFacade::getEntityNameByEntity($insertEntity),
                    null,
                    null,
                    $newReadableValue ?? $insertEntity->getId(),
                    $insertEntity->getId(),
                ));
            }

            foreach ($scheduledCollection->getDeleteDiff() as $deleteEntity) {
                $loggableSetup = $this->loggableEntityConfigFactory->getLoggableSetupByEntity($deleteEntity);
                $oldReadableValue = $this->entityLogFacade->getEntityReadableIdentifierByEntityAndLoggableSetup(
                    $deleteEntity,
                    $loggableSetup,
                );

                $collectionChanges->deletedItems[] = (new ResolvedChanges(
                    EntityLogFacade::getEntityNameByEntity($deleteEntity),
                    $oldReadableValue ?? $deleteEntity->getId(),
                    $deleteEntity->getId(),
                    null,
                    null,
                ));
            }

            if (count($collectionChanges->deletedItems) === 0 && count($collectionChanges->insertedItems) === 0) {
                continue;
            }

            $assoc = $scheduledCollection->getMapping();
            $collectionFieldName = $assoc['fieldName'];
            $resolvedChangedCollections[$collectionFieldName] = $collectionChanges;
        }

        return $resolvedChangedCollections;
    }
}
