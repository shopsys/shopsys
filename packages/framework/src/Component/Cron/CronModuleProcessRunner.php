<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use Shopsys\FrameworkBundle\Command\CronModuleRunnerCommand;
use Symfony\Component\Process\Process;

class CronModuleProcessRunner
{
    public const string RESULT_SUCCESS = 'success';
    public const string RESULT_FAILED = 'failed';

    public function __construct(
        protected readonly string $projectDir,
    ) {
    }

    public function runModule(
        string $serviceId,
        string $instanceName,
        callable $processOutputCallback,
        bool $useAnsiOutput,
        ?string $runId = null,
    ): string {
        $process = $this->createProcess(
            $serviceId,
            $instanceName,
            $useAnsiOutput,
            $runId,
        );

        $process->run($processOutputCallback);

        return $process->isSuccessful() ? static::RESULT_SUCCESS : static::RESULT_FAILED;
    }

    protected function createProcess(
        string $serviceId,
        string $instanceName,
        bool $useAnsiOutput,
        ?string $runId = null,
    ): Process {
        $command = [
            PHP_BINARY,
            $this->projectDir . '/bin/console',
            CronModuleRunnerCommand::COMMAND_NAME,
            $serviceId,
            '--instance-name=' . $instanceName,
        ];

        if ($runId !== null) {
            $command[] = '--run-id=' . $runId;
        }

        $command[] = $useAnsiOutput ? '--ansi' : '--no-ansi';

        return new Process(
            $command,
            $this->projectDir,
            timeout: null,
        );
    }
}
