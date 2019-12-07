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
            $this->em->flush($cronModule);
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
        $this->em->flush($cronModule);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig $cronModuleConfig
     */
    public function suspendModule(CronModuleConfig $cronModuleConfig)
    {
        $cronModule = $this->cronModuleRepository->getCronModuleByServiceId($cronModuleConfig->getServiceId());
        $cronModule->suspend();
        $this->em->flush($cronModule);
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
        $cronModule->setActive(true);
        $cronModule->updateLastStartedAt();

        $this->em->flush($cronModule);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig $cronModuleConfig
     */
    public function markCronAsEnded(CronModuleConfig $cronModuleConfig): void
    {
        $cronModule = $this->cronModuleRepository->getCronModuleByServiceId($cronModuleConfig->getServiceId());
        $cronModule->setActive(false);
        $cronModule->updateLastFinishedAt();

        if ($cronModule->getLastFinishedAt() !== null && $cronModule->getLastStartedAt() !== null) {
            $lastCronDuration = $cronModule->getLastFinishedAt()->getTimestamp() - $cronModule->getLastStartedAt()->getTimestamp();
            $cronModule->setLastDuration($lastCronDuration);
        }

        $this->em->flush($cronModule);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig $cronModuleConfig
     */
    public function markCronAsFailed(CronModuleConfig $cronModuleConfig): void
    {
        $cronModule = $this->cronModuleRepository->getCronModuleByServiceId($cronModuleConfig->getServiceId());
        $cronModule->setFailed();

        $this->em->flush($cronModule);
    }

    /**
     * @param string $serviceId
     * @param bool $enabled
     */
    public function switchCronModule(string $serviceId, bool $enabled): void
    {
        $cronModule = $this->getCronModuleByServiceId($serviceId);
        $cronModule->setEnabled($enabled);

        $this->em->flush($cronModule);
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

        $this->em->flush($cronModule);
    }
}
