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
     *
     * @var string[]
     */
    public const array EXCLUDED_PACKAGES = [
        // excluded from the initial tagging as there needs to be another commit with composer.lock and package-lock.json
        // @see https://github.com/shopsys/shopsys/pull/1264
        'shopsys/shopsys',
        'shopsys/project-base',
    ];

    /**
     * @param \Shopsys\Releaser\Packagist\PackageProvider $packageProvider
     */
    public function __construct(private readonly PackageProvider $packageProvider)
    {
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return 'Create and push git tags for packages excluding monorepo and project-base';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $packages = $this->packageProvider->getPackagesByOrganization('shopsys', array_merge(parent::EXCLUDED_PACKAGES, self::EXCLUDED_PACKAGES));
        $packageNames = str_replace('shopsys/', '', $packages);

        $versionString = $version->getOriginalString();

        $tempDirectory = trim($this->processRunner->run('mktemp -d -t shopsys-release-XXXX'));
        $packageNamesWithProblems = [];

        $this->symfonyStyle->note(sprintf(
            'In case you do not have saved GIT credentials you may want to cache them temporarily so you do not need to fill them for each repository.'
            . ' This can be done by using following command `%s`',
            'git config --global credential.helper "cache --timeout=3600"',
        ));

        $gitCredentialsResponse = $this->symfonyStyle->ask(
            'Do you want to enable saving GIT credentials for one hour?',
            'yes',
        );

        if ($gitCredentialsResponse === 'yes') {
            $this->processRunner->run('git config --global credential.helper "cache --timeout=3600"');
        }

        $this->processRunner->run('git checkout ' . $initialBranchName);

        $this->symfonyStyle->note(
            'You will be asked for your Github credentials if you have not saved them yet.
            As we require two factor authentication, you will need to provide repo scope token instead of password.
            Token can be generated here: https://github.com/settings/tokens/new',
        );

        $this->symfonyStyle->note('Cloning all packages. Please wait.');

        foreach ($packageNames as $packageName) {
            $this->symfonyStyle->note(sprintf('Cloning shopsys/%s. This can take a while.', $packageName));
            $this->processRunner->run(
                sprintf('cd %s && git clone https://github.com/shopsys/%s.git', $tempDirectory, $packageName),
            );
            $this->processRunner->run(
                sprintf(
                    'cd %s/%s && git checkout %s && git tag %s',
                    $tempDirectory,
                    $packageName,
                    $this->currentBranchName,
                    $versionString,
                ),
            );
        }

        foreach ($packageNames as $packageName) {
            $output = $this->processRunner->run(
                sprintf(
                    'cd %s/%s && git log --graph --oneline --decorate=short --color | head',
                    $tempDirectory,
                    $packageName,
                ),
            );

            $this->symfonyStyle->writeln(trim($output));

            $pushTag = $this->symfonyStyle->ask(
                sprintf('Package shopsys/%s: Is the tag on right commit and should be pushed?', $packageName),
                'yes',
            );

            if ($pushTag !== 'yes') {
                $packageNamesWithProblems[] = $packageName;
            }
        }

        if (count($packageNamesWithProblems) === 0) {
            foreach ($packageNames as $packageName) {
                $this->processRunner->run(
                    sprintf('cd %s/%s && git push origin %s', $tempDirectory, $packageName, $versionString),
                );
            }

            $this->processRunner->run('rm -r ' . $tempDirectory);
            $this->symfonyStyle->note(
                'Wait 10 seconds for packagist to get new versions of all packages excluding monorepo and project-base',
            );

            sleep(10);

            $this->checkAllPackagesHaveTag($packageNames, $versionString);
        } else {
            $packageNamesWithProblemsMessage = sprintf(
                'package%s %s',
                count($packageNamesWithProblems) === 1 ? '' : 's',
                implode(', ', $packageNamesWithProblems),
            );
            $this->confirm(
                sprintf(
                    'Please fix the problem in %s and split the monorepo again. This step will be repeated after you confirm.',
                    $packageNamesWithProblemsMessage,
                ),
            );
            $this->processRunner->run('rm -r ' . $tempDirectory);
            $this->work($version);
        }
    }

    /**
     * @param string $packageName
     * @param string $versionString
     * @return bool
     */
    private function checkPackageTagExists(string $packageName, string $versionString): bool
    {
        $url = sprintf(
            'https://github.com/shopsys/%s/releases/tag/%s',
            $packageName,
            $versionString,
        );

        $headers = get_headers($url, true);

        return $headers[0] === 'HTTP/1.1 200 OK';
    }

    /**
     * @return string[]
     */
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE];
    }

    /**
     * @param string[] $packageNames
     * @param string $versionString
     */
    private function checkAllPackagesHaveTag(array $packageNames, string $versionString): void
    {
        $allPackagesHaveTag = true;

        foreach ($packageNames as $packageName) {
            $packageExists = $this->checkPackageTagExists($packageName, $versionString);

            if ($packageExists) {
                $this->symfonyStyle->note(sprintf('Package %s has tag %s released on GitHub.', $packageName, $versionString));
            } else {
                $this->symfonyStyle->error(sprintf('Tag %s has not been found for package %s on GitHub.', $versionString, $packageName));
                $allPackagesHaveTag = false;
            }
        }

        if ($allPackagesHaveTag) {
            return;
        }

        $runChecksAgain = $this->symfonyStyle->ask('Run the checks again?', 'yes');

        if ($runChecksAgain) {
            $this->checkAllPackagesHaveTag($packageNames, $versionString);
        }
    }
}
