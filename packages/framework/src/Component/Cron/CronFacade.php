<?php

namespace Shopsys\FrameworkBundle\Component\Cron;

use DateTimeInterface;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Swift_TransportException;
use Symfony\Bridge\Monolog\Logger;
use Throwable;

class CronFacade
{
    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig
     */
    protected $cronConfig;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade
     */
    protected $cronModuleFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\Mailer
     */
    protected $mailer;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Cron\CronModuleExecutor
     */
    protected $cronModuleExecutor;

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig $cronConfig
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade $cronModuleFacade
     * @param \Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronModuleExecutor $cronModuleExecutor
     */
    public function __construct(
        Logger $logger,
        CronConfig $cronConfig,
        CronModuleFacade $cronModuleFacade,
        Mailer $mailer,
        CronModuleExecutor $cronModuleExecutor
    ) {
        $this->logger = $logger;
        $this->cronConfig = $cronConfig;
        $this->cronModuleFacade = $cronModuleFacade;
        $this->mailer = $mailer;
        $this->cronModuleExecutor = $cronModuleExecutor;
    }

    /**
     * @param \DateTimeInterface $roundedTime
     */
    public function scheduleModulesByTime(DateTimeInterface $roundedTime)
    {
        $cronModuleConfigsToSchedule = $this->cronConfig->getCronModuleConfigsByTime($roundedTime);
        $this->cronModuleFacade->scheduleModules($cronModuleConfigsToSchedule);
    }

    /**
     * @param string $instanceName
     */
    public function runScheduledModulesForInstance(string $instanceName): void
    {
        $cronModuleConfigs = $this->cronConfig->getCronModuleConfigsForInstance($instanceName);

        $scheduledCronModuleConfigs = $this->cronModuleFacade->getOnlyScheduledCronModuleConfigs($cronModuleConfigs);
        $this->runModules($scheduledCronModuleConfigs, $instanceName);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[] $cronModuleConfigs
     * @param string $instanceName
     */
    protected function runModules(array $cronModuleConfigs, string $instanceName): void
    {
        $this->logger->addInfo(sprintf('====== Start of cron instance %s ======', $instanceName));

        foreach ($cronModuleConfigs as $cronModuleConfig) {
            $this->runSingleModule($cronModuleConfig);
            if ($this->cronModuleExecutor->canRun() === false) {
                break;
            }
        }

        $this->logger->addInfo(sprintf('======= End of cron instance %s =======', $instanceName));
    }

    /**
     * @param string $serviceId
     */
    public function runModuleByServiceId($serviceId)
    {
        $cronModuleConfig = $this->cronConfig->getCronModuleConfigByServiceId($serviceId);

        $this->runSingleModule($cronModuleConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig $cronModuleConfig
     */
    protected function runSingleModule(CronModuleConfig $cronModuleConfig)
    {
        if ($this->cronModuleFacade->isModuleDisabled($cronModuleConfig) === true) {
            return;
        }

        $this->logger->addInfo('Start of ' . $cronModuleConfig->getServiceId());
        $cronModuleService = $cronModuleConfig->getService();
        $cronModuleService->setLogger($this->logger);
        $this->cronModuleFacade->markCronAsStarted($cronModuleConfig);

        try {
            $status = $this->cronModuleExecutor->runModule(
                $cronModuleService,
                $this->cronModuleFacade->isModuleSuspended($cronModuleConfig)
            );
        } catch (Throwable $throwable) {
            $this->cronModuleFacade->markCronAsFailed($cronModuleConfig);
            $this->logger->addError('End of ' . $cronModuleConfig->getServiceId() . ' because of error', [
                'throwable' => $throwable,
            ]);
            throw $throwable;
        }

        $this->cronModuleFacade->markCronAsEnded($cronModuleConfig);

        try {
            $this->mailer->flushSpoolQueue();
        } catch (Swift_TransportException $exception) {
            $this->logger->addError(
                'An exception occurred while flushing email queue. Message: "{message}"',
                ['exception' => $exception, 'message' => $exception->getMessage()]
            );
        }

        if ($status === CronModuleExecutor::RUN_STATUS_OK) {
            $this->cronModuleFacade->unscheduleModule($cronModuleConfig);
            $this->logger->addInfo('End of ' . $cronModuleConfig->getServiceId());
        } elseif ($status === CronModuleExecutor::RUN_STATUS_SUSPENDED) {
            $this->cronModuleFacade->suspendModule($cronModuleConfig);
            $this->logger->addInfo('Suspend ' . $cronModuleConfig->getServiceId());
        } elseif ($status === CronModuleExecutor::RUN_STATUS_TIMEOUT) {
            $this->logger->info('Cron reached timeout.');
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[]
     */
    public function getAll()
    {
        return $this->cronConfig->getAllCronModuleConfigs();
    }

    /**
     * @param string $instanceName
     * @return \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[]
     */
    public function getAllForInstance(string $instanceName): array
    {
        return $this->cronConfig->getCronModuleConfigsForInstance($instanceName);
    }

    /**
     * @return string[]
     */
    public function getInstanceNames(): array
    {
        return array_unique(array_map(function (CronModuleConfig $config) {
            return $config->getInstanceName();
        }, $this->getAll()));
    }
}
