<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class WatchdogFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(WatchdogData $watchdogData): Watchdog
    {
        $entityClassName = $this->entityNameResolver->resolve(Watchdog::class);

        return new $entityClassName($watchdogData);
    }
}
