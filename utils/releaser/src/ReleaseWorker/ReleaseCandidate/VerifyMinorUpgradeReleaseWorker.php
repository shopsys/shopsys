<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;
use Symfony\Component\Process\Exception\ProcessFailedException;

final class VerifyMinorUpgradeReleaseWorker extends AbstractShopsysReleaseWorker
{
    private const string ROAVE_PACKAGE = 'roave/backward-compatibility-check';
    private const string ROAVE_BINARY_RELATIVE_PATH = 'vendor/bin/roave-backward-compatibility-check';

    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return 'Verify there are no BC-breaks (runs for minor and patch releases only)';
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        if ($this->isMajorRelease($version)) {
            $this->symfonyStyle->note(sprintf(
                'Skipping BC-break check: %s is a major release where BC-breaks are allowed.',
                $version->getVersionString(),
            ));

            return;
        }

        if ($this->isRoaveBinaryAvailable()) {
            $this->runRoaveCheck();

            return;
        }

        $this->symfonyStyle->note(sprintf('Installing %s as a temporary dev dependency.', self::ROAVE_PACKAGE));
        $this->processRunner->run(sprintf('composer require --dev --no-interaction %s', self::ROAVE_PACKAGE));

        try {
            $this->runRoaveCheck();
        } finally {
            $this->symfonyStyle->note(sprintf('Removing temporary dev dependency %s.', self::ROAVE_PACKAGE));

            try {
                $this->processRunner->run(sprintf('composer remove --dev --no-interaction %s', self::ROAVE_PACKAGE));
            } catch (ProcessFailedException $exception) {
                $this->symfonyStyle->warning(sprintf(
                    'Failed to remove %s automatically: %s. Please clean composer.json and composer.lock manually.',
                    self::ROAVE_PACKAGE,
                    trim($exception->getProcess()->getErrorOutput()),
                ));
            }
        }
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE_CANDIDATE];
    }

    private function isMajorRelease(Version $version): bool
    {
        return $version->getMinor()->getValue() === 0 && $version->getPatch()->getValue() === 0;
    }

    private function isRoaveBinaryAvailable(): bool
    {
        $binaryPath = getcwd() . '/' . self::ROAVE_BINARY_RELATIVE_PATH;

        return is_file($binaryPath) && is_executable($binaryPath);
    }

    private function runRoaveCheck(): void
    {
        $this->symfonyStyle->note('Running roave/backward-compatibility-check. This can take a while.');

        try {
            $output = $this->processRunner->run(self::ROAVE_BINARY_RELATIVE_PATH);
            $this->symfonyStyle->writeln($output);
            $this->success();
        } catch (ProcessFailedException $exception) {
            $process = $exception->getProcess();
            $this->symfonyStyle->writeln(trim($process->getOutput()));
            $errorOutput = trim($process->getErrorOutput());

            if ($errorOutput !== '') {
                $this->symfonyStyle->writeln($errorOutput);
            }

            $this->symfonyStyle->warning('roave/backward-compatibility-check reported potential BC-breaks. Minor releases should have none.');
            $this->confirm('Confirm the reported changes are not real BC-breaks for our consumers and we may continue.');
        }
    }
}
