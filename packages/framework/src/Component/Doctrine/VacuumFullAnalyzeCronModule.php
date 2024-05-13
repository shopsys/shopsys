<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use NinjaMutex\Lock\LockInterface;
use Override;
use Shopsys\FrameworkBundle\Command\CronLockCommand;
use Shopsys\FrameworkBundle\Component\Cron\CronFacade;
use Shopsys\FrameworkBundle\Component\Cron\MutexFactory;
use Shopsys\FrameworkBundle\Component\Maintenance\MaintenanceModeSubscriber;
use Shopsys\FrameworkBundle\Component\Redis\RedisClientFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpClient\Exception\TimeoutException;

class VacuumFullAnalyzeCronModule implements SimpleCronModuleInterface
{
    protected const int ONE_HOUR_IN_SECONDS = 3600;

    protected const string VACUUM_CRON_INSTANCE_NAME = 'vacuum';

    protected Logger $logger;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Shopsys\FrameworkBundle\Component\Redis\RedisClientFacade $redisClientFacade
     * @param \NinjaMutex\Lock\LockInterface $lock
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronFacade $cronFacade
     * @param \Shopsys\FrameworkBundle\Component\Cron\MutexFactory $mutexFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly RedisClientFacade $redisClientFacade,
        protected readonly LockInterface $lock,
        protected readonly CronFacade $cronFacade,
        protected readonly MutexFactory $mutexFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function run(): void
    {
        if (!$this->lockAllCronModules()) {
            return;
        }

        try {
            $this->waitUntilOtherCronModulesOff();
            $this->maintenanceOn();
            $this->makeVacuum();
        } catch (TimeoutException) {
            $this->logger->error(sprintf('The vacuum command was not executed because it was not possible to stop all cron modules within the time limit of %d seconds.', static::ONE_HOUR_IN_SECONDS));
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        } finally {
            $this->maintenanceOff();
            $this->unlockAllCronModules();
        }
    }

    protected function unlockAllCronModules(): void
    {
        $this->lock->releaseLock(CronLockCommand::CRON_MUTEX_LOCK_NAME);
        $this->logger->info('Cron lock was released.');
    }

    protected function waitUntilOtherCronModulesOff(): void
    {
        $endTime = time() + self::ONE_HOUR_IN_SECONDS;
        $cronInstanceNames = $this->cronFacade->getInstanceNames();

        // exclude from checking "vacuum" cron instance
        foreach ($cronInstanceNames as $key => $cronInstanceName) {
            if ($cronInstanceName === self::VACUUM_CRON_INSTANCE_NAME) {
                unset($cronInstanceNames[$key]);
            }
        }

        $mutexLockByCronInstance = array_map(
            fn ($cronInstanceName) => $this->mutexFactory->getPrefixedCronMutex($cronInstanceName),
            $cronInstanceNames,
        );

        $this->logger->info('Waiting till all the cron instances are finished.');

        do {
            $isAnyCronRunning = false;

            foreach ($mutexLockByCronInstance as $mutexLock) {
                if ($mutexLock->isLocked() === true) {
                    $isAnyCronRunning = true;
                }
            }

            if (time() > $endTime) {
                throw new TimeoutException();
            }
        } while ($isAnyCronRunning === true);
    }

    /**
     * @return bool
     */
    protected function lockAllCronModules(): bool
    {
        if (!$this->lock->acquireLock(CronLockCommand::CRON_MUTEX_LOCK_NAME, 0)) {
            $this->logger->error('Cron locking failed.');

            return false;
        }

        $this->logger->info('Cron is now locked.');

        return true;
    }

    protected function makeVacuum(): void
    {
        $this->logger->info('Start of database vacuum');
        $this->entityManager->getConnection()->executeQuery('VACUUM FULL ANALYSE');
        $this->logger->info('End of database vacuum');
    }

    protected function maintenanceOn(): void
    {
        $this->logger->info('Turning on maintenance page');

        if ($this->redisClientFacade->contains(MaintenanceModeSubscriber::MAINTENANCE_KEY) === false) {
            $this->redisClientFacade->save(MaintenanceModeSubscriber::MAINTENANCE_KEY, true);
        }
    }

    protected function maintenanceOff(): void
    {
        $this->logger->info('Turning off maintenance page');
        $this->redisClientFacade->delete(MaintenanceModeSubscriber::MAINTENANCE_KEY);
    }
}
