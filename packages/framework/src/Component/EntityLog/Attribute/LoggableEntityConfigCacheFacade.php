<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Attribute;

class LoggableEntityConfigCacheFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfig[]
     */
    protected array $loggableEntityConfigCache = [];

    public function addLoggableEntityConfig(LoggableEntityConfig $loggableEntityConfig): void
    {
        $this->loggableEntityConfigCache[$loggableEntityConfig->getEntityName()] = $loggableEntityConfig;
    }

    public function findLoggableEntityConfig(string $entityName): ?LoggableEntityConfig
    {
        return $this->loggableEntityConfigCache[$entityName] ?? null;
    }
}
