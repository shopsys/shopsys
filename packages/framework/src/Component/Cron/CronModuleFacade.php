<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;

class CronModuleFacade
{
    protected const string CRON_CACHE_NAMESPACE = 'cron';
    protected const string DURATIONS_CACHE_KEY = 'durations';
    protected const string CRON_MODULES_CACHE_KEY = 'cronModules';

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CronModuleRepository $cronModuleRepository,
        protected readonly CronFilter $cronFilter,
        protected readonly CronModuleRunFactory $cronModuleRunFactory,
        protected readonly InMemoryCache $inMemoryCache,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[] $cronModuleConfigs
     */
    public function scheduleModules(array $cronModuleConfigs): void
    {
        $allModulesIndexedByServiceId = $this->findAllIndexedByServiceId();

        foreach ($cronModuleConfigs as $cronModuleConfig) {
            $serviceId = $cronModuleConfig->getServiceId();
            $cronModule = $allModulesIndexedByServiceId[$serviceId] ?? $this->cronModuleRepository->getCronModuleByServiceId($serviceId);
            $cronModule->schedule();
        }

        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[] $cronModuleConfigs
     * @return \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[]
     */
    public function getOnlyScheduledCronModuleConfigs(array $cronModuleConfigs): array
    {
        $scheduledServiceIds = $this->cronModuleRepository->getAllScheduledCronModuleServiceIds();

        return $this->cronFilter->filterScheduledCronModuleConfigs($cronModuleConfigs, $scheduledServiceIds);
    }

    public function unscheduleModule(CronModuleConfig $cronModuleConfig): void
    {
        $cronModule = $this->cronModuleRepository->getCronModuleByServiceId($cronModuleConfig->getServiceId());
        $cronModule->unschedule();
        $this->em->flush();
    }

    public function suspendModule(CronModuleConfig $cronModuleConfig): void
    {
        $cronModule = $this->cronModuleRepository->getCronModuleByServiceId($cronModuleConfig->getServiceId());
        $cronModule->suspend();
        $this->em->flush();
    }

    public function isModuleDisabled(CronModuleConfig $cronModuleConfig): bool
    {
        $cronModule = $this->cronModuleRepository->getCronModuleByServiceId($cronModuleConfig->getServiceId());

        return $cronModule->isEnabled() === false;
    }

    public function isModuleSuspended(CronModuleConfig $cronModuleConfig): bool
    {
        $cronModule = $this->cronModuleRepository->getCronModuleByServiceId($cronModuleConfig->getServiceId());

        return $cronModule->isSuspended();
    }

    public function markCronAsStarted(CronModuleConfig $cronModuleConfig): void
    {
        $cronModule = $this->cronModuleRepository->getCronModuleByServiceId($cronModuleConfig->getServiceId());
        $cronModule->setStatusRunning();
        $cronModule->updateLastStartedAt();

        $this->em->flush();
    }

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

    public function markCronAsFailed(CronModuleConfig $cronModuleConfig): void
    {
        $cronModule = $this->cronModuleRepository->getCronModuleByServiceId($cronModuleConfig->getServiceId());
        $startedAt = $cronModule->getLastStartedAt() ?? $this->clock->now();
        $finishedAt = $this->clock->now();
        $lastCronDuration = max(0, $finishedAt->getTimestamp() - $startedAt->getTimestamp());

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
                'now' => $finishedAt->format('Y-m-d H:i:s'),
                'lastDuration' => $lastCronDuration,
            ],
        );

        $connection->executeStatement(
            'INSERT INTO cron_module_runs (cron_module_id, status, started_at, finished_at, duration) 
                VALUES (:cronModuleId, :status, :startedAt, :finishedAt, :duration)',
            [
                'cronModuleId' => $cronModule->getServiceId(),
                'status' => CronModule::CRON_STATUS_ERROR,
                'startedAt' => $startedAt->format('Y-m-d H:i:s'),
                'finishedAt' => $finishedAt->format('Y-m-d H:i:s'),
                'duration' => $lastCronDuration,
            ],
        );
    }

    public function disableCronModuleByServiceId(string $serviceId): void
    {
        $cronModule = $this->getCronModuleByServiceId($serviceId);
        $cronModule->disable();

        $this->em->flush();
    }

    public function enableCronModuleByServiceId(string $serviceId): void
    {
        $cronModule = $this->getCronModuleByServiceId($serviceId);
        $cronModule->enable();

        $this->em->flush();
    }

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

    public function schedule(string $serviceId): void
    {
        $cronModule = $this->getCronModuleByServiceId($serviceId);
        $cronModule->schedule();

        $this->em->flush();
    }

    /**
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

    public function deleteOldCronModuleRuns(int $numberOfDays): void
    {
        $this->cronModuleRepository->deleteOldCronModuleRuns($numberOfDays);
    }

    public function getRunsByCronModuleQueryBuilder(CronModule $cronModule): QueryBuilder
    {
        return $this->cronModuleRepository->getRunsByCronModuleQueryBuilder($cronModule);
    }
}
