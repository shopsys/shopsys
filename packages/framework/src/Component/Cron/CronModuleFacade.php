<?php

namespace Shopsys\FrameworkBundle\Component\Cron;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;

class CronModuleFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator
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
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $em
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
    public function isModuleSuspended(CronModuleConfig $cronModuleConfig)
    {
        $cronModule = $this->cronModuleRepository->getCronModuleByServiceId($cronModuleConfig->getServiceId());

        return $cronModule->isSuspended();
    }
}
