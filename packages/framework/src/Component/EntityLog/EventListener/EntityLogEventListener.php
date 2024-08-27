<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfig;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfigFactory;
use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ChangeSetResolver;
use Shopsys\FrameworkBundle\Component\EntityLog\Enum\EntityLogActionEnum;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade;
use Symfony\Contracts\Service\ResetInterface;

class EntityLogEventListener implements ResetInterface
{
    protected ?string $logCollectionNumber;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Psr\Log\LoggerInterface $monolog
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfigFactory $loggableEntityConfigFactory
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ChangeSetResolver $changeSetResolver
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade $entityLogFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly LoggerInterface $monolog,
        protected readonly LoggableEntityConfigFactory $loggableEntityConfigFactory,
        protected readonly ChangeSetResolver $changeSetResolver,
        protected readonly EntityLogFacade $entityLogFacade,
    ) {
        $this->logCollectionNumber = null;
    }

    /**
     * @param \Doctrine\ORM\Event\PostPersistEventArgs $args
     */
    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $this->log(EntityLogActionEnum::CREATE, $entity);
    }

    /**
     * @param \Doctrine\ORM\Event\PostUpdateEventArgs $args
     */
    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $this->log(EntityLogActionEnum::UPDATE, $entity);
    }

    /**
     * @param \Doctrine\ORM\Event\PreRemoveEventArgs $args
     */
    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        $this->log(EntityLogActionEnum::DELETE, $entity);
    }

    /**
     * @param string $action
     * @param object $entity
     */
    protected function log(string $action, object $entity): void
    {
        $loggableSetup = $this->loggableEntityConfigFactory->getLoggableSetupByEntity($entity);

        try {
            if (!$loggableSetup->isLoggable()) {
                return;
            }

            $this->registerLog($entity, $loggableSetup, $action);
        } catch (Throwable $exception) {
            $this->monolog->error($exception->getMessage());
        }
    }

    /**
     * @param object $entity
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfig $loggableSetup
     * @param string $action
     */
    protected function registerLog(
        object $entity,
        LoggableEntityConfig $loggableSetup,
        string $action,
    ): void {
        $resolvedChangeSet = [];

        if ($action === EntityLogActionEnum::UPDATE) {
            $resolvedChangeSet = $this->resolveUpdateChangeSet($entity);

            if (count($resolvedChangeSet) === 0) {
                return;
            }
        }

        if ($this->logCollectionNumber === null) {
            $this->logCollectionNumber = uniqid('entityLog', true);
        }

        $entityLog = $this->entityLogFacade->createEntityLog($entity, $loggableSetup, $action, $resolvedChangeSet, $this->logCollectionNumber);
        $this->em->persist($entityLog);
        $this->em->flush();
    }

    /**
     * @param object $entity
     * @return array
     */
    protected function resolveUpdateChangeSet(object $entity): array
    {
        $resolvedChangeSet = [];
        $unitOfWork = $this->em->getUnitOfWork();

        $scheduledCollectionUpdates = $unitOfWork->getScheduledCollectionUpdates();

        if (count($scheduledCollectionUpdates) > 0) {
            $resolvedChangeSet = array_merge($resolvedChangeSet, $this->changeSetResolver->resolveChangesOnCollectionForEntity($scheduledCollectionUpdates, $entity));
        }

        $scheduledCollectionDeletions = $unitOfWork->getScheduledCollectionDeletions();

        if (count($scheduledCollectionDeletions) > 0) {
            $resolvedChangeSet = array_merge($resolvedChangeSet, $this->changeSetResolver->resolveChangesOnCollectionForEntity($scheduledCollectionDeletions, $entity));
        }

        $changeSet = $unitOfWork->getEntityChangeSet($entity);

        if (count($changeSet) > 0) {
            $resolvedChangeSet = array_merge($resolvedChangeSet, $this->changeSetResolver->resolveChangeSetForEntity($changeSet, $entity));
        }

        return $resolvedChangeSet;
    }

    public function reset(): void
    {
        $this->logCollectionNumber = null;
    }
}
