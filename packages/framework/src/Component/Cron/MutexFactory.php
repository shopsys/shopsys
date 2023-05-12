<?php

namespace Shopsys\FrameworkBundle\Component\Cron;

use NinjaMutex\Lock\LockInterface;
use NinjaMutex\Mutex;

class MutexFactory
{
    protected const MUTEX_CRON_NAME = 'cron';

    /**
     * @var \NinjaMutex\Mutex[]
     */
    protected array $mutexesByName;

    /**
     * @param \NinjaMutex\Lock\LockInterface $lock
     */
    public function __construct(protected readonly LockInterface $lock)
    {
        $this->mutexesByName = [];
    }

    /**
     * @param string $prefix
     * @return \NinjaMutex\Mutex
     */
    public function getPrefixedCronMutex(string $prefix): Mutex
    {
        return $this->getMutexByName($prefix . '-' . static::MUTEX_CRON_NAME);
    }

    /**
     * @param string $mutexName
     * @return \NinjaMutex\Mutex
     */
    protected function getMutexByName(string $mutexName): Mutex
    {
        if (!array_key_exists($mutexName, $this->mutexesByName)) {
            $this->mutexesByName[$mutexName] = new Mutex($mutexName, $this->lock);
        }

        return $this->mutexesByName[$mutexName];
    }
}
