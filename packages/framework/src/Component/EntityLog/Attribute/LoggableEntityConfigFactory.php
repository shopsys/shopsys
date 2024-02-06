<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Attribute;

use Doctrine\ORM\EntityManagerInterface;
use ReflectionAttribute;
use ReflectionClass;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade;

class LoggableEntityConfigFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfigCacheFacade $loggableSetupCacheFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly LoggableEntityConfigCacheFacade $loggableSetupCacheFacade,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param object|string $objectOrClass
     * @return \Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfig
     */
    public function getLoggableSetupByEntity(object|string $objectOrClass): LoggableEntityConfig
    {
        $entityNameData = EntityLogFacade::getEntityNameDataByEntity($objectOrClass);
        $loggableSetup = $this->loggableSetupCacheFacade->findLoggableEntityConfig($entityNameData->getShortName());

        if ($loggableSetup !== null) {
            return $loggableSetup;
        }

        $reflectionClass = new ReflectionClass($entityNameData->getFullyQualifiedName());
        $loggableAttributes = $reflectionClass->getAttributes(Loggable::class, ReflectionAttribute::IS_INSTANCEOF);
        $isLoggable = count($loggableAttributes) > 0;

        $loggableSetup = new LoggableEntityConfig(
            $entityNameData->getShortName(),
            $entityNameData->getFullyQualifiedName(),
            $isLoggable && method_exists($entityNameData->getFullyQualifiedName(), 'getId'),
        );

        $this->initIdentificationOfEntity($loggableSetup, $reflectionClass);

        if ($loggableSetup->isLoggable()) {
            $loggableSetup->setStrategy($this->getStrategyByReflectionClass($reflectionClass));
            $this->initLoggableUnderParent($loggableSetup, $reflectionClass);
            $this->initLoggableProperties($loggableSetup, $reflectionClass);
        }

        $this->loggableSetupCacheFacade->addLoggableEntityConfig($loggableSetup);

        return $loggableSetup;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfig $loggableSetup
     * @param \ReflectionClass $reflectionClass
     */
    protected function initLoggableProperties(
        LoggableEntityConfig $loggableSetup,
        ReflectionClass $reflectionClass,
    ): void {
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $logPropertyAttributes = $reflectionProperty->getAttributes(Log::class, ReflectionAttribute::IS_INSTANCEOF);

            if (count($logPropertyAttributes) > 0) {
                $loggableSetup->addIncludedPropertyName($reflectionProperty->getName());

                continue;
            }

            $excludeLogPropertyAttributes = $reflectionProperty->getAttributes(ExcludeLog::class, ReflectionAttribute::IS_INSTANCEOF);

            if (count($excludeLogPropertyAttributes) > 0) {
                $loggableSetup->addExcludedPropertyName($reflectionProperty->getName());
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfig $loggableSetup
     * @param \ReflectionClass $reflectionClass
     */
    protected function initLoggableUnderParent(
        LoggableEntityConfig $loggableSetup,
        ReflectionClass $reflectionClass,
    ): void {
        $loggableChildAttributes = $reflectionClass->getAttributes(LoggableChild::class, ReflectionAttribute::IS_INSTANCEOF);

        if (count($loggableChildAttributes) === 0) {
            return;
        }

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $loggableParentPropertyAttributes = $reflectionProperty->getAttributes(LoggableParentProperty::class, ReflectionAttribute::IS_INSTANCEOF);

            if (count($loggableParentPropertyAttributes) === 0) {
                continue;
            }

            $classMetaData = $this->em->getClassMetadata($loggableSetup->getEntityFullyQualifiedName());

            $associationMapping = $classMetaData->getAssociationMapping($reflectionProperty->getName());

            $targetEntity = $associationMapping['targetEntity'];
            $targetEntityNamespaceParts = explode('\\', $targetEntity);
            $targetEntityObjectName = array_pop($targetEntityNamespaceParts);

            $loggableSetup->setParentEntityName($targetEntityObjectName);

            $referencedColumnName = $associationMapping['joinColumns'][0]['referencedColumnName'] ?? false;
            $expectedMethodName = sprintf('get%s', ucfirst($referencedColumnName));

            if ($referencedColumnName && method_exists($targetEntity, $expectedMethodName)) {
                $loggableSetup->setParentEntityIdentityFunctionName($expectedMethodName);
            }

            break;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfig $loggableSetup
     * @param \ReflectionClass $reflectionClass
     */
    protected function initIdentificationOfEntity(
        LoggableEntityConfig $loggableSetup,
        ReflectionClass $reflectionClass,
    ): void {
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $identifyAttributes = $reflectionMethod->getAttributes(EntityLogIdentify::class, ReflectionAttribute::IS_INSTANCEOF);

            if (count($identifyAttributes) === 0) {
                continue;
            }

            $isLocalized = $identifyAttributes[0]->getArguments()[0] ?? false;

            $loggableSetup->setEntityReadableNameFunctionName($reflectionMethod->getName());
            $loggableSetup->setIsLocalized($isLocalized);

            break;
        }
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @return string|null
     */
    protected function getStrategyByReflectionClass(ReflectionClass $reflectionClass): ?string
    {
        $attributes = $reflectionClass->getAttributes(Loggable::class, ReflectionAttribute::IS_INSTANCEOF);

        if (count($attributes) > 0) {
            return $attributes[0]->getArguments()[0] ?? null;
        }

        return null;
    }
}
