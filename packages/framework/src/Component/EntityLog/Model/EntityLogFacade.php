<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Model;

use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfig;
use Shopsys\FrameworkBundle\Component\EntityLog\Detection\DetectionFacade;
use Shopsys\FrameworkBundle\Component\EntityLog\Exception\NotLoggableException;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class EntityLogFacade
{
    public function __construct(
        protected readonly Localization $localization,
        protected readonly DetectionFacade $detectionFacade,
        protected readonly EntityLogFactory $entityLogFactory,
        protected readonly EntityLogDataFactory $entityLogDataFactory,
        protected readonly AdministratorLocalizationFacade $administratorLocalizationFacade,
    ) {
    }

    public function getEntityNameByEntity(object|string $objectOrClass): string
    {
        return $this->getEntityNameDataByEntity($objectOrClass)->getShortName();
    }

    public function getEntityNameDataByEntity(object|string $objectOrClass): EntityNameData
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

    public function createEntityLog(
        object $entity,
        LoggableEntityConfig $loggableEntityConfig,
        string $action,
        array $changes = [],
    ): EntityLog {
        $userIdentifier = $this->detectionFacade->getUserIdentifier();
        $source = $this->detectionFacade->getEntityLogSource();
        $parentEntityFunctionName = $loggableEntityConfig->getParentEntityFunctionName();
        $parentEntityIdentityFunctionName = $loggableEntityConfig->getParentEntityIdentityFunctionName();
        $parentEntity = $parentEntityFunctionName ? call_user_func([$entity, $parentEntityFunctionName]) : null;

        $entityLogData = $this->entityLogDataFactory->create();
        $entityLogData->action = $action;
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

    protected function getEntityIdentifierByEntityAndLoggableSetup(object $entity): int
    {
        if (method_exists($entity, 'getId')) {
            return $entity->getId();
        }

        throw new NotLoggableException(sprintf('Entity %s without ID as primary key is not loggable.', $entity::class));
    }

    public function getEntityReadableIdentifierByEntityAndLoggableSetup(
        object $entity,
        LoggableEntityConfig $loggableSetup,
    ): ?string {
        $functionName = $loggableSetup->getEntityReadableNameFunctionName();

        if ($functionName === null) {
            return null;
        }

        if ($loggableSetup->isLocalized()) {
            return call_user_func([$entity, $functionName], $this->getLocaleForEntityLog());
        }

        return call_user_func([$entity, $functionName]);
    }

    public function getLocaleForEntityLog(): string
    {
        $defaultAdminLocale = $this->administratorLocalizationFacade->getDefaultAdminLocale();

        return $this->localization->getFallbackLocaleIfLocaleIsNotUsedOnAnyDomain($defaultAdminLocale);
    }
}
