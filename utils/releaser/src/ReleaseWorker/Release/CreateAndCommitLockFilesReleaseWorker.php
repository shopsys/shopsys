<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\Release;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class CreateAndCommitLockFilesReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return 'Create and commit composer.lock, symfony.lock, package-lock.json, and migrations-lock.yml and [Manually] push it';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(Version $version, string $initialBranchName = 'master'): void
    {
        $currentDir = trim($this->processRunner->run('pwd'));

        $packageName = 'project-base';
        $tempDirectory = trim($this->processRunner->run('mktemp -d -t shopsys-release-XXXX'));

        $this->symfonyStyle->note(sprintf('Cloning shopsys/%s. This can take a while.', $packageName));
        $this->processRunner->run(
            sprintf('cd %s && git clone --branch=%s https://github.com/shopsys/%s.git', $tempDirectory, $this->currentBranchName, $packageName)
        );

        $this->symfonyStyle->note('Installing dependencies');
        $this->processRunner->run(sprintf('cd %s/project-base && composer update && npm install', $tempDirectory));
        $this->processRunner->run(sprintf('cd %s/project-base && php phing db-rebuild', $tempDirectory));

        $this->symfonyStyle->note('Committing changes in composer.lock, symfony.lock, package-lock.json, and migrations-lock.yml');
        $this->processRunner->run(sprintf('cp %s/project-base/composer.lock %s/project-base/composer.lock', $tempDirectory, $currentDir));
        $this->processRunner->run(sprintf('cp %s/project-base/symfony.lock %s/project-base/symfony.lock', $tempDirectory, $currentDir));
        $this->processRunner->run(sprintf('cp %s/project-base/package-lock.json %s/project-base/package-lock.json', $tempDirectory, $currentDir));
        $this->processRunner->run(sprintf('cp %s/project-base/migrations-lock.yml %s/project-base/migrations-lock.yml', $tempDirectory, $currentDir));

        $this->processRunner->run('git add -f project-base/composer.lock');
        $this->processRunner->run('git add project-base/symfony.lock');
        $this->processRunner->run('git add -f project-base/package-lock.json');
        $this->processRunner->run('git add -f project-base/migrations-lock.yml');

        $message = sprintf('locked versions of dependencies for %s release', $version->getVersionString());
        $this->commit($message);

        $this->symfonyStyle->note([
            'Push last commit with generated lock files',
            'You have to allow push to the protected branch here https://github.com/shopsys/shopsys/settings/branches first',
        ]);

        $this->symfonyStyle->confirm(
            sprintf(
                'confirm that composer.lock, symfony.lock, package-lock.json, and migrations-lock.yml are pushed to "%s" branch',
                $this->currentBranchName
            )
        );
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE;
    }
}
