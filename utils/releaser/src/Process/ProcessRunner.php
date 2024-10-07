<?php

declare(strict_types=1);

namespace Shopsys\Releaser\Process;

use Shopsys\Releaser\Command\SymfonyStyleFactory;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ProcessRunner
{
    private const int PROCESS_TIMEOUT = 20 * 60;

    /**
     * @param \Shopsys\Releaser\Command\SymfonyStyleFactory $symfonyStyleFactory
     */
    public function __construct(
        private readonly SymfonyStyleFactory $symfonyStyleFactory,
    ) {
    }

    /**
     * @param string $command
     * @return string
     */
    public function run(string $command): string
    {
        $symfonyStyle = $this->symfonyStyleFactory->getPreviouslyCreatedSymfonyStyle();

        if ($symfonyStyle->isVerbose()) {
            $symfonyStyle->note('Running process: ' . $command);
        }

        $process = Process::fromShellCommandline($command, timeout: self::PROCESS_TIMEOUT);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return trim($process->getOutput());
    }
}
