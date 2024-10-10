<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use PharIo\Version\Version;
use Shopsys\Releaser\FileManipulator\DockerfileVersionFileManipulator;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class SetPhpImageVersionInDockerfileReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \Shopsys\Releaser\FileManipulator\DockerfileVersionFileManipulator $dockerfileVersionFileManipulator
     */
    public function __construct(
        private readonly DockerfileVersionFileManipulator $dockerfileVersionFileManipulator,
    ) {
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
        return sprintf(
            'Set %s in Dockerfile to "%s" version',
            AbstractShopsysReleaseWorker::PHP_IMAGE_PACKAGE_NAME,
            $this->getDevelopmentVersionString($version),
        );
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
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

    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    private function getDevelopmentVersionString(Version $version): string
    {
        return $version->getMajor()->getValue() . '.' . $version->getMinor()->getValue();
    }

    /**
     * @return string[]
     */
    protected function getAllowedStages(): array
    {
        return [Stage::AFTER_RELEASE];
    }
}
