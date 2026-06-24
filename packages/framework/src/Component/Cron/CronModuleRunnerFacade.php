<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use Monolog\Logger;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\FrameworkBundle\Component\Reflection\ReflectionHelper;
use Throwable;

class CronModuleRunnerFacade
{
    public function __construct(
        protected readonly Logger $logger,
        protected readonly CronConfig $cronConfig,
        protected readonly CronModuleFacade $cronModuleFacade,
        protected readonly CronModuleExecutor $cronModuleExecutor,
        protected readonly SentryCronMonitorFacade $sentryCronMonitorFacade,
    ) {
    }

    public function runModuleByServiceIdInContext(
        string $serviceId,
        ?string $instanceName = null,
        ?string $runId = null,
    ): string {
        $this->logger->pushProcessor(static function ($record) use ($instanceName, $runId) {
            if ($instanceName !== null) {
                $record->extra['instance'] = $instanceName;
            }

            if ($runId !== null) {
                $record->extra['runId'] = $runId;
            }

            return $record;
        });

        try {
            $cronModuleConfig = $this->cronConfig->getCronModuleConfigByServiceId($serviceId);

            return $this->runSingleModule($cronModuleConfig);
        } finally {
            $this->logger->popProcessor();
        }
    }

    protected function runSingleModule(CronModuleConfig $cronModuleConfig): string
    {
        if ($this->cronModuleFacade->isModuleDisabled($cronModuleConfig) === true) {
            $this->cronModuleFacade->unscheduleModule($cronModuleConfig);
            $this->sentryCronMonitorFacade->reportDisabledRunAsHealthy($cronModuleConfig);

            return CronModuleExecutor::RUN_STATUS_OK;
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

                return CronModuleExecutor::RUN_STATUS_ERROR;
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
        } finally {
            $this->logger->popProcessor();
        }


        return $status;
    }
}
