<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\PersistentCollection;
use Override;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfig;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfigFactory;
use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ChangeSetResolver;
use Shopsys\FrameworkBundle\Component\EntityLog\Enum\EntityLogActionEnum;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogNoteRegistry;
use Shopsys\FrameworkBundle\Component\EntityLog\ParentResolver\EntityLogParentResolver;
use Symfony\Contracts\Service\ResetInterface;
use Throwable;

class EntityLogEventListener implements ResetInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog[]
     */
    protected array $logs = [];

    public function __construct(
        protected readonly EntityManagerInterface $entityLogEntityManager,
        protected readonly EntityManagerInterface $applicationEntityManager,
        protected readonly LoggerInterface $monolog,
        protected readonly LoggableEntityConfigFactory $loggableEntityConfigFactory,
        protected readonly ChangeSetResolver $changeSetResolver,
        protected readonly EntityLogFacade $entityLogFacade,
        protected readonly EntityLogNoteRegistry $entityLogNoteRegistry,
        protected readonly EntityLogParentResolver $entityLogParentResolver,
    ) {
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        // `note` explains a single flush
        // it has to be dropped even when no log was created,
        // otherwise it could end up on an unrelated later change of the same entity
        $this->entityLogNoteRegistry->reset();

        if (count($this->logs) <= 0) {
            return;
        }

        $logCollectionNumber = uniqid('entityLog', true);

        foreach ($this->logs as $log) {
            $log->setLogCollectionNumber($logCollectionNumber);
            $this->entityLogEntityManager->persist($log);
        }

        $this->logs = [];
        $this->entityLogEntityManager->flush();
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $this->log(EntityLogActionEnum::CREATE, $entity);
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $this->log(EntityLogActionEnum::UPDATE, $entity);
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        $this->log(EntityLogActionEnum::DELETE, $entity);
    }

    protected function log(string $action, object $entity): void
    {
        $loggableSetup = $this->loggableEntityConfigFactory->getLoggableSetupByEntity($entity);

        if (!$loggableSetup->isLoggable()) {
            return;
        }

        try {
            $parentEntityWithoutAssociation = null;

            if ($loggableSetup->getParentPropertyName() === null && $this->entityLogParentResolver->supports($entity)) {
                $parentEntityWithoutAssociation = $this->entityLogParentResolver->resolveParent($entity);

                // a child of an entity that is not logged is not logged either (e.g. images of products)
                if ($parentEntityWithoutAssociation === null || !$this->loggableEntityConfigFactory->getLoggableSetupByEntity($parentEntityWithoutAssociation)->isLoggable()) {
                    return;
                }
            }

            $this->registerLog($entity, $loggableSetup, $action, $parentEntityWithoutAssociation);
        } catch (Throwable $exception) {
            $this->monolog->error($exception->getMessage());
        }
    }

    protected function registerLog(
        object $entity,
        LoggableEntityConfig $loggableSetup,
        string $action,
        ?object $parentEntityWithoutAssociation = null,
    ): void {
        $resolvedChangeSet = [];

        if ($action === EntityLogActionEnum::UPDATE) {
            $resolvedChangeSet = $this->resolveUpdateChangeSet($entity);

            if (count($resolvedChangeSet) === 0) {
                return;
            }
        }

        $this->logs[] = $this->entityLogFacade->createEntityLog($entity, $loggableSetup, $action, $resolvedChangeSet, $parentEntityWithoutAssociation);
    }

    protected function resolveUpdateChangeSet(object $entity): array
    {
        $resolvedChangeSet = [];
        $unitOfWork = $this->applicationEntityManager->getUnitOfWork();

        $scheduledCollectionUpdates = $unitOfWork->getScheduledCollectionUpdates();

        if (count($scheduledCollectionUpdates) > 0) {
            $resolvedChangeSet = array_merge($resolvedChangeSet, $this->changeSetResolver->resolveChangesOnCollectionForEntity($scheduledCollectionUpdates, $entity));
        }

        $scheduledCollectionDeletions = $unitOfWork->getScheduledCollectionDeletions();

        if (count($scheduledCollectionDeletions) > 0) {
            $resolvedChangeSet = array_merge($resolvedChangeSet, $this->changeSetResolver->resolveChangesOnCollectionForEntity($scheduledCollectionDeletions, $entity));
        }

        $changeSet = $unitOfWork->getEntityChangeSet($entity);

        foreach ($changeSet as $key => $value) {
            if ($value instanceof PersistentCollection) {
                unset($changeSet[$key]); // collection is logged in resolveChangesOnCollectionForEntity method
            }
        }

        if (count($changeSet) > 0) {
            $resolvedChangeSet = array_merge($resolvedChangeSet, $this->changeSetResolver->resolveChangeSetForEntity($changeSet, $entity));
        }

        return $resolvedChangeSet;
    }

    #[Override]
    public function reset(): void
    {
        $this->logs = [];
    }
}
