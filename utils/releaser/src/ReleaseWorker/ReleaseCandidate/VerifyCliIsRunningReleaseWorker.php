<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class VerifyCliIsRunningReleaseWorker extends AbstractShopsysReleaseWorker
{
    private const string PROJECT_NAME = 'rc-project';
    private const string TEMP_DIRECTORY_BASE = '/tmp/shopsys-cli-verify';

    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return 'Verify that Shopsys Cli is able to run on the new version';
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $this->symfonyStyle->note([
            '1) Install composer dependencies in packages/cli',
            '2) In some temporary directory use Cli to init a project',
            sprintf('path-to-project/packages/cli init rc-project -b %s', $this->currentBranchName),
            '3) Verify it finishes without errors.',
        ]);

        if (!$this->symfonyStyle->confirm('Do you want me to run it now?', false)) {
            $this->confirm('Confirm the Shopsys Cli is able to run on the new version.');

            return;
        }

        $this->runCliVerification();
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE_CANDIDATE];
    }

    private function runCliVerification(): void
    {
        $cliDirectory = getcwd() . '/packages/cli';
        $tempDirectory = self::TEMP_DIRECTORY_BASE . '-' . $this->currentBranchName;
        $escapedCliDirectory = escapeshellarg($cliDirectory);
        $escapedTempDirectory = escapeshellarg($tempDirectory);
        $escapedProjectName = escapeshellarg(self::PROJECT_NAME);
        $escapedBranch = escapeshellarg($this->currentBranchName);

        $this->symfonyStyle->note('Installing composer dependencies in packages/cli');
        $this->processRunner->run(sprintf(
            'cd %s && composer install --no-interaction',
            $escapedCliDirectory,
        ));

        $this->symfonyStyle->note('Preparing temporary directory: ' . $tempDirectory);
        $this->processRunner->run(sprintf(
            'rm -rf %s && mkdir -p %s',
            $escapedTempDirectory,
            $escapedTempDirectory,
        ));

        $this->symfonyStyle->note('Running Shopsys CLI init (the configure step will abort once it hits closed stdin; only the clone is verified)');
        $this->processRunner->run(sprintf(
            'cd %s && %s/bin/shopsys init %s -b %s < /dev/null || true',
            $escapedTempDirectory,
            $escapedCliDirectory,
            $escapedProjectName,
            $escapedBranch,
        ));

        $this->symfonyStyle->note('Verifying that project-base was cloned by the CLI');
        $this->processRunner->run(sprintf(
            'test -d %s/%s/.git',
            $escapedTempDirectory,
            $escapedProjectName,
        ));

        $this->symfonyStyle->note('Cleaning up the temporary directory');
        $this->processRunner->run(sprintf('rm -rf %s', $escapedTempDirectory));

        $this->symfonyStyle->success('Shopsys CLI installed composer dependencies and successfully cloned project-base.');
    }
}
