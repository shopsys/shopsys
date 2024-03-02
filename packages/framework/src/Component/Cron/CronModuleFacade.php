<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;

class CronModuleFacade
{
    protected const string CRON_CACHE_NAMESPACE = 'cron';
    protected const string DURATIONS_CACHE_KEY = 'durations';
    protected const string CRON_MODULES_CACHE_KEY = 'cronModules';

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronModuleRepository $cronModuleRepository
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronFilter $cronFilter
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronModuleRunFactory $cronModuleRunFactory
     * @param \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache $inMemoryCache
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CronModuleRepository $cronModuleRepository,
        protected readonly CronFilter $cronFilter,
        protected readonly CronModuleRunFactory $cronModuleRunFactory,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[] $cronModuleConfigs
     */
    public function scheduleModules(array $cronModuleConfigs)
    {
        foreach ($cronModuleConfigs as $cronModuleConfig) {
            $cronModule = $this->cronModuleRepository->getCronModuleByServiceId($cronModuleConfig->getServiceId());
            $cronModule->schedule();
            $this->em->flush();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[] $cronModuleConfigs
     * @return \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[]
     */
    public function getOnlyScheduledCronModuleConfigs(array $cronModuleConfigs)
    {
        $scheduledServiceIds = $this->cronModuleRepository->getAllScheduledCronModuleServiceIds();

        return $this->cronFilter->filterScheduledCronModuleConfigs($cronModuleConfigs, $scheduledServiceIds);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig $cronModuleConfig
     */
    public function unscheduleModule(CronModuleConfig $cronModuleConfig)
    {
        $cronModule = $this->cronModuleRepository->getCronModuleByServiceId($cronModuleConfig->getServiceId());
        $cronModule->unschedule();
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig $cronModuleConfig
     */
    public function suspendModule(CronModuleConfig $cronModuleConfig)
    {
        $cronModule = $this->cronModuleRepository->getCronModuleByServiceId($cronModuleConfig->getServiceId());
        $cronModule->suspend();
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig $cronModuleConfig
     * @return bool
     */
    public function isModuleDisabled(CronModuleConfig $cronModuleConfig): bool
    {
        $cronModule = $this->cronModuleRepository->getCronModuleByServiceId($cronModuleConfig->getServiceId());

        return $cronModule->isEnabled() === false;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig $cronModuleConfig
     * @return bool
     */
    public function isModuleSuspended(CronModuleConfig $cronModuleConfig)
    {
        $cronModule = $this->cronModuleRepository->getCronModuleByServiceId($cronModuleConfig->getServiceId());

        return $cronModule->isSuspended();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig $cronModuleConfig
     */
    public function markCronAsStarted(CronModuleConfig $cronModuleConfig): void
    {
        $cronModule = $this->cronModuleRepository->getCronModuleByServiceId($cronModuleConfig->getServiceId());
        $cronModule->setStatusRunning();
        $cronModule->updateLastStartedAt();

        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig $cronModuleConfig
     */
    public function markCronAsEnded(CronModuleConfig $cronModuleConfig): void
    {
        $cronModule = $this->cronModuleRepository->getCronModuleByServiceId($cronModuleConfig->getServiceId());
        $cronModule->setStatusOk();
        $cronModule->updateLastFinishedAt();

        if ($cronModule->getLastStartedAt() !== null) {
            $lastCronDuration = $cronModule->getLastFinishedAt()->getTimestamp() - $cronModule->getLastStartedAt()->getTimestamp();
            $cronModule->setLastDuration($lastCronDuration);
        }

        $cronModuleRun = $this->cronModuleRunFactory->createFromFinishedCronModule($cronModule);
        $this->em->persist($cronModuleRun);

        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig $cronModuleConfig
     */
    public function markCronAsFailed(CronModuleConfig $cronModuleConfig): void
    {
        $cronModule = $this->cronModuleRepository->getCronModuleByServiceId($cronModuleConfig->getServiceId());
        $lastCronDuration = time() - $cronModule->getLastStartedAt()->getTimestamp();

        /**
         * We want to avoid flushing the whole identity map (using EntityManager::flush())
         * because CRON is marked as failed when an unexpected exception occurs
         * and in such a case we do not want to propagate all the current changes to the database
         * therefore the native query for setting CRON error status is used here.
         */
        $connection = $this->em->getConnection();

        if (!$connection->isConnected()) {
            return;
        }

        $connection->executeStatement(
            'UPDATE cron_modules 
            SET 
                status = :errorStatus,
                last_finished_at = :now,
                last_duration = :lastDuration
            WHERE service_id = :serviceId',
            [
                'errorStatus' => CronModule::CRON_STATUS_ERROR,
                'serviceId' => $cronModuleConfig->getServiceId(),
                'now' => (new DateTime())->format('Y-m-d H:i:s'),
                'lastDuration' => $lastCronDuration,
            ],
        );

        $connection->executeStatement(
            'INSERT INTO cron_module_runs (cron_module_id, status, started_at, finished_at, duration) 
                VALUES (:cronModuleId, :status, :startedAt, :finishedAt, :duration)',
            [
                'cronModuleId' => $cronModule->getServiceId(),
                'status' => CronModule::CRON_STATUS_ERROR,
                'startedAt' => $cronModule->getLastStartedAt()->format('Y-m-d H:i:s'),
                'finishedAt' => (new DateTime())->format('Y-m-d H:i:s'),
                'duration' => $lastCronDuration,
            ],
        );
    }

    /**
     * @param string $serviceId
     */
    public function disableCronModuleByServiceId(string $serviceId): void
    {
        $cronModule = $this->getCronModuleByServiceId($serviceId);
        $cronModule->disable();

        $this->em->flush();
    }

    /**
     * @param string $serviceId
     */
    public function enableCronModuleByServiceId(string $serviceId): void
    {
        $cronModule = $this->getCronModuleByServiceId($serviceId);
        $cronModule->enable();

        $this->em->flush();
    }

    /**
     * @param string $serviceId
     * @return \Shopsys\FrameworkBundle\Component\Cron\CronModule
     */
    public function getCronModuleByServiceId(string $serviceId): CronModule
    {
        return $this->cronModuleRepository->getCronModuleByServiceId($serviceId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Cron\CronModule[]
     */
    public function findAllIndexedByServiceId(): array
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::CRON_CACHE_NAMESPACE,
            fn () => $this->cronModuleRepository->findAllIndexedByServiceId(),
            static::CRON_MODULES_CACHE_KEY,
        );
    }

    /**
     * @param string $serviceId
     */
    public function schedule(string $serviceId): void
    {
        $cronModule = $this->getCronModuleByServiceId($serviceId);
        $cronModule->schedule();

        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronModule $cronModule
     * @return \Shopsys\FrameworkBundle\Component\Cron\CronModuleRun[]
     */
    public function getAllRunsByCronModule(CronModule $cronModule): array
    {
        return $this->cronModuleRepository->getAllRunsByCronModule($cronModule);
    }

    /**
     * @return array<string, array{cronModuleId: string, minimalDuration: string, maximalDuration: string, averageDuration: string}>
     */
    public function getCronCalculatedDurationsIndexedByServiceId(): array
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::CRON_CACHE_NAMESPACE,
            fn () => $this->cronModuleRepository->getCronCalculatedDurationsIndexedByServiceId(),
            static::DURATIONS_CACHE_KEY,
        );
    }

    /**
     * @param int $numberOfDays
     */
    public function deleteOldCronModuleRuns(int $numberOfDays): void
    {
        $this->cronModuleRepository->deleteOldCronModuleRuns($numberOfDays);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronModule $cronModule
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRunsByCronModuleQueryBuilder(CronModule $cronModule): QueryBuilder
    {
        return $this->cronModuleRepository->getRunsByCronModuleQueryBuilder($cronModule);
    }
}
