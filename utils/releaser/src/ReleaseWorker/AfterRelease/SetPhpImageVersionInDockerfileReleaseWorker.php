<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\FileManipulator\DockerfileVersionFileManipulator;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class SetPhpImageVersionInDockerfileReleaseWorker extends AbstractShopsysReleaseWorker
{
    public function __construct(
        private readonly DockerfileVersionFileManipulator $dockerfileVersionFileManipulator,
    ) {
    }

    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return sprintf(
            'Set %s in Dockerfile to "%s" version',
            AbstractShopsysReleaseWorker::PHP_IMAGE_PACKAGE_NAME,
            $this->getDevelopmentVersionString($version),
        );
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $developmentVersion = $this->getDevelopmentVersionString($version);

        $this->dockerfileVersionFileManipulator->updateDockerFileVersion($developmentVersion);

        $this->commit(
            sprintf(
                '%s base image version in Dockerfile set to "%s"',
                AbstractShopsysReleaseWorker::PHP_IMAGE_PACKAGE_NAME,
                $developmentVersion,
            ),
        );

        $this->confirm(
            sprintf('Confirm you have pushed the new commit into the "%s" branch', $this->currentBranchName),
        );
    }

    private function getDevelopmentVersionString(Version $version): string
    {
        return $version->getMajor()->getValue() . '.' . $version->getMinor()->getValue();
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::AFTER_RELEASE];
    }
}
