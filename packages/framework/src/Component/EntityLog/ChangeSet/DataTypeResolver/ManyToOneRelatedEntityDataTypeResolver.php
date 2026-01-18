<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver;

use Override;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfigFactory;
use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade;

class ManyToOneRelatedEntityDataTypeResolver extends AbstractDataTypeResolver
{
    public function __construct(
        protected readonly LoggableEntityConfigFactory $loggableEntityConfigFactory,
        protected readonly EntityLogFacade $entityLogFacade,
    ) {
    }

    #[Override]
    protected function isResolvedDataType(mixed $value): bool
    {
        return is_object($value) && method_exists($value, 'getId');
    }

    /**
     * @param array{0: mixed, 1: mixed} $changes
     */
    #[Override]
    public function getResolvedChanges(array $changes): ResolvedChanges
    {
        $oldValue = $changes[0];
        $newValue = $changes[1];

        $oldReadableValue = $oldValue?->getId();

        if ($oldValue) {
            $oldValueLoggableSetup = $this->loggableEntityConfigFactory->getLoggableSetupByEntity($oldValue);

            if ($oldValueLoggableSetup->isEntityIdentifiable()) {
                $oldReadableValue = $this->entityLogFacade->getEntityReadableIdentifierByEntityAndLoggableSetup(
                    $oldValue,
                    $oldValueLoggableSetup,
                );
            }
        }

        $newReadableValue = $newValue?->getId();

        if ($newValue) {
            $newValueLoggableSetup = $this->loggableEntityConfigFactory->getLoggableSetupByEntity($newValue);

            if ($newValueLoggableSetup->isEntityIdentifiable()) {
                $newReadableValue = $this->entityLogFacade->getEntityReadableIdentifierByEntityAndLoggableSetup(
                    $newValue,
                    $newValueLoggableSetup,
                );
            }
        }

        return new ResolvedChanges(
            $this->entityLogFacade->getEntityNameByEntity($oldValue ?? $newValue),
            $oldReadableValue,
            $oldValue?->getId(),
            $newReadableValue,
            $newValue?->getId(),
        );
    }

    #[Override]
    public function getPriority(): int
    {
        return 1;
    }
}
