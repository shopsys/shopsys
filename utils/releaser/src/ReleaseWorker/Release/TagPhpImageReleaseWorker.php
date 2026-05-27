<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\Release;

use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\FileManipulator\DockerfileVersionFileManipulator;
use Shopsys\Releaser\Guzzle\ApiCaller;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;
use Shopsys\Releaser\Wait\DockerHubTagAvailable;

final class TagPhpImageReleaseWorker extends AbstractShopsysReleaseWorker
{
    public function __construct(
        private readonly DockerfileVersionFileManipulator $dockerfileVersionFileManipulator,
        private readonly ApiCaller $apiCaller,
    ) {
    }

    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return sprintf('Tag %s with the same version as the release and replace FROM in Dockerfile', AbstractShopsysReleaseWorker::PHP_IMAGE_PACKAGE_NAME);
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $tempDirectory = trim($this->processRunner->run('mktemp -d -t shopsys-release-XXXX'));
        $versionString = $version->getOriginalString();

        $this->symfonyStyle->note(sprintf('Cloning shopsys/%s. This can take a while.', AbstractShopsysReleaseWorker::PHP_IMAGE_PACKAGE_NAME));
        $this->processRunner->run(
            sprintf('cd %s && git clone https://github.com/shopsys/%s.git', $tempDirectory, AbstractShopsysReleaseWorker::PHP_IMAGE_PACKAGE_NAME),
        );

        $this->processRunner->run(
            sprintf(
                'cd %s/%s && git checkout %s && git tag %s',
                $tempDirectory,
                AbstractShopsysReleaseWorker::PHP_IMAGE_PACKAGE_NAME,
                $initialBranchName,
                $versionString,
            ),
        );

        $output = $this->processRunner->run(
            sprintf(
                'cd %s/%s && git log --graph --oneline --decorate=short --color | head',
                $tempDirectory,
                AbstractShopsysReleaseWorker::PHP_IMAGE_PACKAGE_NAME,
            ),
        );

        $this->symfonyStyle->writeln(trim($output));

        $isTaggedProperly = $this->symfonyStyle->ask(
            sprintf('Package shopsys/%s: Is the tag on right commit and should be pushed?', AbstractShopsysReleaseWorker::PHP_IMAGE_PACKAGE_NAME),
            'yes',
        );

        if (!$isTaggedProperly) {
            $this->confirm(
                sprintf('Please fix the problem in shopsys/%s and split the monorepo again. This step will be repeated after you confirm.', AbstractShopsysReleaseWorker::PHP_IMAGE_PACKAGE_NAME),
            );
            $this->processRunner->run('rm -r ' . $tempDirectory);
            $this->work($version);

            return;
        }

        $this->processRunner->run(
            sprintf('cd %s/%s && git push origin %s', $tempDirectory, AbstractShopsysReleaseWorker::PHP_IMAGE_PACKAGE_NAME, $versionString),
        );


        $this->processRunner->run('rm -r ' . $tempDirectory);
        $this->symfonyStyle->note(
            sprintf('You can track progress on https://github.com/shopsys/%s/actions', AbstractShopsysReleaseWorker::PHP_IMAGE_PACKAGE_NAME),
        );

        $this->waitFor(new DockerHubTagAvailable(
            $this->apiCaller,
            'shopsys',
            AbstractShopsysReleaseWorker::PHP_IMAGE_PACKAGE_NAME,
            $versionString,
        ));

        $this->dockerfileVersionFileManipulator->updateDockerFileVersion($versionString);

        $infoMessage = sprintf(
            '%s base image version in Dockerfile set to "%s"',
            AbstractShopsysReleaseWorker::PHP_IMAGE_PACKAGE_NAME,
            $versionString,
        );
        $this->symfonyStyle->note($infoMessage);

        $this->commit($infoMessage);

        $this->success();
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE];
    }
}
