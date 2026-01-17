<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Maintenance;

use Shopsys\FrameworkBundle\Component\Redis\RedisClientFacade;

class MaintenanceModeFacade
{
    public const string MAINTENANCE_KEY = 'maintenance';

    protected ?bool $isMaintenanceModeEnabled = null;

    public function __construct(
        protected readonly RedisClientFacade $redisClientFacade,
    ) {
    }

    public function disable(): void
    {
        $this->redisClientFacade->delete(static::MAINTENANCE_KEY);
        $this->isMaintenanceModeEnabled = false;
    }

    public function enable(): void
    {
        $this->redisClientFacade->save(static::MAINTENANCE_KEY, true);
        $this->isMaintenanceModeEnabled = true;
    }

    public function isEnabled(): bool
    {
        if ($this->isMaintenanceModeEnabled === null) {
            $this->isMaintenanceModeEnabled = $this->redisClientFacade->contains(static::MAINTENANCE_KEY);
        }

        return $this->isMaintenanceModeEnabled;
    }
}
