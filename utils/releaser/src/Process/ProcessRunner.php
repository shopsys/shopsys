<?php

declare(strict_types=1);

namespace Shopsys\Releaser\Process;

use Shopsys\Releaser\Command\SymfonyStyleFactory;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ProcessRunner
{
    private const int PROCESS_TIMEOUT = 20 * 60;

    public function __construct(
        private readonly SymfonyStyleFactory $symfonyStyleFactory,
    ) {
    }

    /**
     * @param array<string, string> $env
     */
    public function run(string $command, array $env = [], ?string $cwd = null): string
    {
        $symfonyStyle = $this->symfonyStyleFactory->getPreviouslyCreatedSymfonyStyle();

        if ($symfonyStyle->isVerbose()) {
            $symfonyStyle->note('Running process: ' . $command);
        }

        $process = Process::fromShellCommandline($command, cwd: $cwd, timeout: self::PROCESS_TIMEOUT, env: $env);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return trim($process->getOutput());
    }
}
