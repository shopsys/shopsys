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
     * @param array<string, string> $env Extra environment variables to expose to the subprocess on top of
     *     the inherited parent environment. Use this to pass secrets (e.g. GITHUB_TOKEN) that the command
     *     consumes via shell-variable expansion, so the secret never appears in the printed command string.
     */
    public function run(string $command, array $env = []): string
    {
        $symfonyStyle = $this->symfonyStyleFactory->getPreviouslyCreatedSymfonyStyle();

        if ($symfonyStyle->isVerbose()) {
            $symfonyStyle->note('Running process: ' . $command);
        }

        $process = Process::fromShellCommandline($command, timeout: self::PROCESS_TIMEOUT, env: $env);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return trim($process->getOutput());
    }
}
