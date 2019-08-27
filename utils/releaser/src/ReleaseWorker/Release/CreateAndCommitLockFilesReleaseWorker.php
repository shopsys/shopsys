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
        return 'Create and commit composer.lock, package-lock.json, and migrations-lock.yml and [Manually] push it';
    }

    /**
     * Higher first
     * @return int
     */
    public function getPriority(): int
    {
        return 630;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $this->symfonyStyle->note('Removing vendor/, node_modules/, composer.lock, migrations-lock.yml, and package-lock.json');
        $this->processRunner->run('rm -rf project-base/vendor');
        $this->processRunner->run('rm -rf project-base/node_modules');
        $this->processRunner->run('rm -f project-base/composer.lock');
        $this->processRunner->run('rm -f project-base/package-lock.json');
        $this->processRunner->run('rm -f project-base/migrations-lock.yml');
        $this->processRunner->run('cd project-base && composer install && npm install');

        $this->processRunner->run('git add -f project-base/composer.lock');
        $this->processRunner->run('git add -f project-base/package-lock.json');

        $this->processRunner->run('rm -rf project-base/vendor');
        $this->processRunner->run('composer install');

        $this->processRunner->run('php phing db-rebuild');
        $this->processRunner->run('git add -f project-base/migrations-lock.yml');

        $message = sprintf('locked versions of dependencies for %s release', $version->getVersionString());
        $this->commit($message);

        $this->symfonyStyle->note('push last commit with composer, package, and migration locks');
        $this->symfonyStyle->confirm(sprintf('confirm that composer.lock, package-lock.json, and migrations-lock.yml are pushed to "%s" branch', $this->initialBranchName));
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE;
    }
}
