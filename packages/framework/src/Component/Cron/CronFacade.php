<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use DateTimeInterface;
use Monolog\Logger;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\FrameworkBundle\Component\Reflection\ReflectionHelper;
use Throwable;

class CronFacade
{
    public function __construct(
        protected readonly Logger $logger,
        protected readonly CronConfig $cronConfig,
        protected readonly CronModuleFacade $cronModuleFacade,
        protected readonly CronModuleExecutor $cronModuleExecutor,
    ) {
    }

    public function scheduleModulesByTime(DateTimeInterface $roundedTime): void
    {
        $cronModuleConfigsToSchedule = $this->cronConfig->getCronModuleConfigsByTime($roundedTime);
        $this->cronModuleFacade->scheduleModules($cronModuleConfigsToSchedule);
    }

    public function runScheduledModulesForInstance(string $instanceName): void
    {
        $cronModuleConfigs = $this->cronConfig->getCronModuleConfigsForInstance($instanceName);

        $scheduledCronModuleConfigs = $this->cronModuleFacade->getOnlyScheduledCronModuleConfigs($cronModuleConfigs);
        $this->runModules($scheduledCronModuleConfigs, $instanceName);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[] $cronModuleConfigs
     */
    protected function runModules(array $cronModuleConfigs, string $instanceName): void
    {
        $unique = uniqid(more_entropy: true);

        $this->logger->pushProcessor(function ($record) use ($instanceName, $unique) {
            $record->extra['instance'] = $instanceName;
            $record->extra['runId'] = $unique;

            return $record;
        });

        $this->logger->info('Start of cron instance');

        try {
            foreach ($cronModuleConfigs as $cronModuleConfig) {
                $this->runSingleModule($cronModuleConfig);

                if ($this->cronModuleExecutor->canRun($cronModuleConfig) === false) {
                    break;
                }
            }
        } catch (Throwable $throwable) {
            $this->logger->error('End of cron instance with error', [
                'exception' => $throwable,
            ]);
            $this->logger->popProcessor();

            return;
        }

        $this->logger->info('End of cron instance');

        $this->logger->popProcessor();
    }

    /**
     * @param string $serviceId
     */
    public function runModuleByServiceId($serviceId): void
    {
        $cronModuleConfig = $this->cronConfig->getCronModuleConfigByServiceId($serviceId);

        $this->runSingleModule($cronModuleConfig);
    }

    protected function runSingleModule(CronModuleConfig $cronModuleConfig): void
    {
        if ($this->cronModuleFacade->isModuleDisabled($cronModuleConfig) === true) {
            return;
        }

        $shortServiceId = ReflectionHelper::getShortClassName($cronModuleConfig->getServiceId());
        $this->logger->pushProcessor(function ($record) use ($shortServiceId) {
            $record->extra['module'] = $shortServiceId;

            return $record;
        });

        $this->logger->info('Cron module started');
        $cronModuleService = $cronModuleConfig->getService();
        $cronModuleService->setLogger($this->logger);
        $this->cronModuleFacade->markCronAsStarted($cronModuleConfig);

        try {
            $status = $this->cronModuleExecutor->runModule(
                $cronModuleService,
                $this->cronModuleFacade->isModuleSuspended($cronModuleConfig),
            );
        } catch (Throwable $throwable) {
            $this->cronModuleFacade->markCronAsFailed($cronModuleConfig);
            $this->logger->error('Cron module ended with error', [
                'exception' => $throwable,
            ]);

            throw $throwable;
        }

        $this->cronModuleFacade->markCronAsEnded($cronModuleConfig);

        if ($status === CronModuleExecutor::RUN_STATUS_OK) {
            $this->cronModuleFacade->unscheduleModule($cronModuleConfig);
        } elseif ($status === CronModuleExecutor::RUN_STATUS_SUSPENDED) {
            $this->cronModuleFacade->suspendModule($cronModuleConfig);
        }

        $this->logger->info('Cron module ended', [
            'status' => $status,
        ]);

        $this->logger->popProcessor();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[]
     */
    public function getAll()
    {
        return $this->cronConfig->getAllCronModuleConfigs();
    }

    /**
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
