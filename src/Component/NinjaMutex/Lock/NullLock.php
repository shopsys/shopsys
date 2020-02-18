<?php

declare(strict_types=1);

namespace App\Component\NinjaMutex\Lock;

use NinjaMutex\Lock\LockAbstract;

class NullLock extends LockAbstract
{
    /**
     * @param  string $name name of lock
     * @param  bool   $blocking
     * @return bool
     */
    protected function getLock($name, $blocking)
    {
        return true;
    }

    /**
     * Release lock
     *
     * @param  string $name name of lock
     * @return bool
     */
    public function releaseLock($name)
    {
        return true;
    }

    /**
     * Check if lock is locked
     *
     * @param  string $name name of lock
     * @return bool
     */
    public function isLocked($name)
    {
        return false;
    }
}
