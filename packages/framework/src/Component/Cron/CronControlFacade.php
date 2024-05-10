<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use NinjaMutex\Lock\LockInterface;
use NinjaMutex\Mutex;
use Symfony\Component\HttpClient\Exception\TimeoutException;

class CronControlFacade
{
    protected const string CRON_MUTEX_LOCK_NAME = 'cronLocker';

    public function __construct(
        protected readonly LockInterface $lock,
        protected readonly CronFacade $cronFacade,
        protected readonly MutexFactory $mutexFactory,
    ) {
    }

    public function isCronLocked(): bool
    {
        return $this->lock->isLocked(static::CRON_MUTEX_LOCK_NAME);
    }

    public function lockCron(): bool
    {
        return $this->lock->acquireLock(static::CRON_MUTEX_LOCK_NAME, 0);
    }

    public function unlockCron(): void
    {
        $this->lock->releaseLock(static::CRON_MUTEX_LOCK_NAME);
    }

    /**
     * @param array<string> $excludedCronInstanceNames
     */
    public function waitUntilCronInstancesAreFinished(
        array $excludedCronInstanceNames = [],
        ?int $timeoutSeconds = null,
    ): void {
        $endTime = $timeoutSeconds !== null ? time() + $timeoutSeconds : null;
        $mutexLocks = $this->getMutexLocksByNonExcludedCronInstance($excludedCronInstanceNames);

        do {
            $isAnyCronRunning = false;

            foreach ($mutexLocks as $mutexLock) {
                if ($mutexLock->isLocked() === true) {
                    $isAnyCronRunning = true;

                    break;
                }
            }

            if ($endTime !== null && time() > $endTime) {
                throw new TimeoutException();
            }
        } while ($isAnyCronRunning === true);
    }

    /**
     * @param array<string> $excludedCronInstanceNames
     * @return array<\NinjaMutex\Mutex>
     */
    protected function getMutexLocksByNonExcludedCronInstance(array $excludedCronInstanceNames): array
    {
        $cronInstanceNames = array_filter(
            $this->cronFacade->getInstanceNames(),
            static fn (string $cronInstanceName): bool => !in_array($cronInstanceName, $excludedCronInstanceNames, true),
        );

        return array_map(
            fn (string $cronInstanceName): Mutex => $this->mutexFactory->getPrefixedCronMutex($cronInstanceName),
            $cronInstanceNames,
        );
    }
}
