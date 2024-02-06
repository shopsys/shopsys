<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Model;

use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfig;
use Shopsys\FrameworkBundle\Component\EntityLog\Detection\DetectionFacade;
use Shopsys\FrameworkBundle\Component\EntityLog\Enum\EntityLogActionEnumInterface;
use Shopsys\FrameworkBundle\Component\EntityLog\Exception\NotLoggableException;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class EntityLogFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogRepository $entityLogRepository
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Detection\DetectionFacade $detectionFacade
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFactory $entityLogFactory
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogDataFactory $entityLogDataFactory
     */
    public function __construct(
        protected readonly EntityLogRepository $entityLogRepository,
        protected readonly Localization $localization,
        protected readonly DetectionFacade $detectionFacade,
        protected readonly EntityLogFactory $entityLogFactory,
        protected readonly EntityLogDataFactory $entityLogDataFactory,
    ) {
    }

    /**
     * @param object|string $objectOrClass
     * @return string
     */
    public static function getEntityNameByEntity(object|string $objectOrClass): string
    {
        return self::getEntityNameDataByEntity($objectOrClass)->getShortName();
    }

    /**
     * @param object|string $objectOrClass
     * @return \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityNameData
     */
    public static function getEntityNameDataByEntity(object|string $objectOrClass): EntityNameData
    {
        $entityFullyQualifiedName = is_string($objectOrClass) ? $objectOrClass : get_class($objectOrClass);
        $entityFullyQualifiedName = str_replace('Proxies\__CG__\\', '', $entityFullyQualifiedName);
        $entityClassNameParts = explode('\\', $entityFullyQualifiedName);

        $className = array_pop($entityClassNameParts);

        return new EntityNameData(
            $entityFullyQualifiedName,
            $className,
        );
    }

    /**
     * @param object $entity
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfig $loggableEntityConfig
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Enum\EntityLogActionEnum $actionEnum
     * @param array $changes
     * @return \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog
     */
    public function createEntityLog(
        object $entity,
        LoggableEntityConfig $loggableEntityConfig,
        EntityLogActionEnumInterface $actionEnum,
        array $changes = [],
    ): EntityLog {
        $userIdentifier = $this->detectionFacade->getUserIdentifier();
        $source = $this->detectionFacade->getEntityLogSource();
        $parentEntityFunctionName = $loggableEntityConfig->getParentEntityFunctionName();
        $parentEntityIdentityFunctionName = $loggableEntityConfig->getParentEntityIdentityFunctionName();
        $parentEntity = $parentEntityFunctionName ? call_user_func([$entity, $parentEntityFunctionName]) : null;

        $entityLogData = $this->entityLogDataFactory->create();
        $entityLogData->action = $actionEnum;
        $entityLogData->userIdentifier = $userIdentifier;
        $entityLogData->entityName = $loggableEntityConfig->getEntityName();
        $entityLogData->entityId = $this->getEntityIdentifierByEntityAndLoggableSetup($entity);
        $entityLogData->entityIdentifier = $this->getEntityReadableIdentifierByEntityAndLoggableSetup($entity, $loggableEntityConfig) ?? '';
        $entityLogData->source = $source;
        $entityLogData->changeSet = $changes;
        $entityLogData->parentEntityName = $loggableEntityConfig->getParentEntityName();
        $entityLogData->parentEntityId = is_object($parentEntity) && $parentEntityIdentityFunctionName !== null
            ?
            call_user_func([$parentEntity, $parentEntityIdentityFunctionName])
            : null;

        return $this->entityLogFactory->create($entityLogData);
    }

    /**
     * @param object $entity
     * @return int
     */
    protected function getEntityIdentifierByEntityAndLoggableSetup(object $entity): int
    {
        if (method_exists($entity, 'getId')) {
            return $entity->getId();
        }

        throw new NotLoggableException(sprintf('Entity %s without ID as primary key is not loggable.', $entity::class));
    }

    /**
     * @param object $entity
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfig $loggableSetup
     * @return string|null
     */
    public function getEntityReadableIdentifierByEntityAndLoggableSetup(
        object $entity,
        LoggableEntityConfig $loggableSetup,
    ): ?string {
        $functionName = $loggableSetup->getEntityReadableNameFunctionName();

        if ($functionName === null) {
            return null;
        }

        if ($loggableSetup->isLocalized()) {
            return call_user_func([$entity, $functionName], $this->localization->getAdminLocale());
        }

        return call_user_func([$entity, $functionName]);
    }
}
