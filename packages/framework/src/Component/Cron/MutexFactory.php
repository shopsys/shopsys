<?php

namespace Shopsys\FrameworkBundle\Component\Cron;

use NinjaMutex\Lock\LockInterface;
use NinjaMutex\Mutex;

class MutexFactory
{
    const MUTEX_CRON_NAME = 'cron';

    /**
     * @var \NinjaMutex\Lock\LockInterface
     */
    private $lock;

    /**
     * @var \NinjaMutex\Mutex[]
     */
    private $mutexesByName;

    public function __construct(LockInterface $lock)
    {
        $this->lock = $lock;
        $this->mutexesByName = [];
    }

    public function getCronMutex(): \NinjaMutex\Mutex
    {
        if (!array_key_exists(self::MUTEX_CRON_NAME, $this->mutexesByName)) {
            $this->mutexesByName[self::MUTEX_CRON_NAME] = new Mutex(self::MUTEX_CRON_NAME, $this->lock);
        }

        return $this->mutexesByName[self::MUTEX_CRON_NAME];
    }
}
