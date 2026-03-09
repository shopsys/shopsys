<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use NinjaMutex\Lock\LockInterface;
use NinjaMutex\Mutex;

class MutexFactory
{
    protected const string MUTEX_CRON_NAME = 'cron';

    /**
     * @var \NinjaMutex\Mutex[]
     */
    protected array $mutexesByName;

    public function __construct(protected readonly LockInterface $lock)
    {
        $this->mutexesByName = [];
    }

    public function getPrefixedCronMutex(string $prefix): Mutex
    {
        return $this->getMutexByName($prefix . '-' . static::MUTEX_CRON_NAME);
    }

    protected function getMutexByName(string $mutexName): Mutex
    {
        if (!array_key_exists($mutexName, $this->mutexesByName)) {
            $this->mutexesByName[$mutexName] = new Mutex($mutexName, $this->lock);
        }

        return $this->mutexesByName[$mutexName];
    }
}
