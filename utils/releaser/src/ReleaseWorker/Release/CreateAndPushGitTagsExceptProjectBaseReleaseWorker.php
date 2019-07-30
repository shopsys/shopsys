<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\Release;

use PharIo\Version\Version;
use Shopsys\Releaser\Packagist\PackageProvider;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class CreateAndPushGitTagsExceptProjectBaseReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * Packages that are not released - old packages or forks
     * @var string[]
     */
    private const EXCLUDED_PACKAGES = [
        // excluded from the initial tagging as there needs to be another commit with composer.lock and package-lock.json
        // @see https://github.com/shopsys/shopsys/pull/1264
        'shopsys/shopsys',
        'shopsys/project-base',
        // not maintained anymore
        'shopsys/product-feed-interface',
        'shopsys/phpstorm-inspect',
        // forks
        'shopsys/postgres-search-bundle',
        'shopsys/doctrine-orm',
        'shopsys/jparser',
        // not related packages
        'shopsys/syscart',
        'shopsys/sysconfig',
        'shopsys/sysreports',
        'shopsys/sysstdlib',
    ];

    /**
     * @var \Shopsys\Releaser\Packagist\PackageProvider
     */
    private $packageProvider;

    /**
     * @param \Shopsys\Releaser\Packagist\PackageProvider $packageProvider
     */
    public function __construct(PackageProvider $packageProvider)
    {
        $this->packageProvider = $packageProvider;
    }

    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return 'Create and push git tags for packages excluding monorepo and project-base';
    }

    /**
     * Higher first
     * @return int
     */
    public function getPriority(): int
    {
        return 640;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $packages = $this->packageProvider->getPackagesByOrganization('shopsys', self::EXCLUDED_PACKAGES);
        $packageNames = str_replace('shopsys/', '', $packages);

        $versionString = $version->getVersionString();

        $tempDirectory = trim($this->processRunner->run('mktemp -d -t shopsys-release-XXXX'));
        $packageNamesWithProblems = [];
        foreach ($packageNames as $packageName) {
            $this->symfonyStyle->note(sprintf('Cloning shopsys/%s. This can take a while.', $packageName));
            $this->processRunner->run(sprintf('cd %s && git clone https://github.com/shopsys/%s.git', $tempDirectory, $packageName));
            $this->processRunner->run(sprintf('cd %s/%s && git checkout master && git tag %s', $tempDirectory, $packageName, $versionString));
            $this->processRunner->run(sprintf('cd %s/%s && git log --graph --oneline --decorate=short --color | head', $tempDirectory, $packageName), true);
            $pushTag = $this->symfonyStyle->ask(sprintf('Package shopsys/%s: Is the tag on right commit and should be pushed?', $packageName), 'yes');

            if ($pushTag !== 'yes') {
                $packageNamesWithProblems[] = $packageName;
            }
        }

        if (count($packageNamesWithProblems) === 0) {
            foreach ($packageNames as $packageName) {
                $this->processRunner->run(sprintf('cd %s/%s && git push origin %s', $tempDirectory, $packageName, $versionString));
            }

            $this->processRunner->run('rm -r ' . $tempDirectory);
            $this->symfonyStyle->note('Wait for packagist to get new versions of all packages excluding monorepo and project-base');
            $this->confirm('Confirm that there are new versions of all packages excluding monorepo and project-base');
        } else {
            $packageNamesWithProblemsMessage = sprintf('package%s %s', count($packageNamesWithProblems) === 1 ? '' : 's', implode(', ', $packageNamesWithProblems));
            $this->confirm(sprintf('Please fix the problem in %s and split the monorepo again. This step will be repeated after you confirm.', $packageNamesWithProblemsMessage));
            $this->processRunner->run('rm -r ' . $tempDirectory);
            $this->work($version);
        }
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE;
    }
}
