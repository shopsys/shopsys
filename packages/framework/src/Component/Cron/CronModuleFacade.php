<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;

class CronModuleFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Cron\CronModuleRepository
     */
    protected $cronModuleRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Cron\CronFilter
     */
    protected $cronFilter;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronModuleRepository $cronModuleRepository
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronFilter $cronFilter
     */
    public function __construct(
        EntityManagerInterface $em,
        CronModuleRepository $cronModuleRepository,
        CronFilter $cronFilter
    ) {
        $this->em = $em;
        $this->cronModuleRepository = $cronModuleRepository;
        $this->cronFilter = $cronFilter;
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

        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig $cronModuleConfig
     */
    public function markCronAsFailed(CronModuleConfig $cronModuleConfig): void
    {
        /**
         * We want to avoid flushing the whole identity map (using EntityManager::flush())
         * because CRON is marked as failed when an unexpected exception occurs
         * and in such a case we do not want to propagate all the current changes to the database
         * therefore the native query for setting CRON error status is used here.
         */
        $connection = $this->em->getConnection();
        if ($connection->isConnected()) {
            $connection->executeStatement('UPDATE cron_modules SET status = :errorStatus WHERE service_id = :serviceId', [
                'errorStatus' => CronModule::CRON_STATUS_ERROR,
                'serviceId' => $cronModuleConfig->getServiceId(),
            ]);
        }
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
        return $this->cronModuleRepository->findAllIndexedByServiceId();
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
}
