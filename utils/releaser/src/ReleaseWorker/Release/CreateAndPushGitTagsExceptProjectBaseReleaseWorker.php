<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\Release;

use Override;
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

    public function __construct(private readonly PackageProvider $packageProvider)
    {
    }

    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return 'Create and push git tags for packages excluding monorepo and project-base';
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $packages = $this->packageProvider->getPackagesByOrganization(AbstractShopsysReleaseWorker::ORGANIZATION, array_merge(parent::EXCLUDED_PACKAGES, self::EXCLUDED_PACKAGES));
        $packageNames = str_replace(AbstractShopsysReleaseWorker::ORGANIZATION . '/', '', $packages);

        $versionString = $version->getOriginalString();

        $tempDirectory = trim($this->processRunner->run('mktemp -d -t shopsys-release-XXXX'));
        $packageNamesWithProblems = [];

        $this->processRunner->run('git checkout ' . $initialBranchName);

        $this->symfonyStyle->note('Cloning all packages. Please wait.');

        foreach ($packageNames as $packageName) {
            $this->symfonyStyle->note(sprintf('Cloning shopsys/%s. This can take a while.', $packageName));
            $this->runWithGithubToken(sprintf(
                '%s clone https://github.com/%s/%s.git %s/%s',
                AbstractShopsysReleaseWorker::AUTHENTICATED_GIT_COMMAND_PREFIX,
                AbstractShopsysReleaseWorker::ORGANIZATION,
                $packageName,
                $tempDirectory,
                $packageName,
            ));
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
                $this->runWithGithubToken(sprintf(
                    'cd %s/%s && %s push origin %s',
                    $tempDirectory,
                    $packageName,
                    AbstractShopsysReleaseWorker::AUTHENTICATED_GIT_COMMAND_PREFIX,
                    $versionString,
                ));
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

    private function checkPackageTagExists(string $packageName, string $versionString): bool
    {
        $url = sprintf(
            'https://github.com/%s/%s/releases/tag/%s',
            AbstractShopsysReleaseWorker::ORGANIZATION,
            $packageName,
            $versionString,
        );

        $headers = @get_headers($url, true);

        return $headers[0] === 'HTTP/1.1 200 OK';
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE];
    }

    /**
     * @param string[] $packageNames
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
