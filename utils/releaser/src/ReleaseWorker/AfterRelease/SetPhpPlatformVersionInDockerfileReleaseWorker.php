<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\AfterRelease;

use PharIo\Version\Version;
use Shopsys\Releaser\FileManipulator\DockerfileVersionFileManipulator;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class SetPhpPlatformVersionInDockerfileReleaseWorker extends AbstractShopsysReleaseWorker
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
            'Set php-platform in Dockerfile to "%s" version',
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
                'php-platform base image version in Dockerfile set to "%s"',
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
     * @return string
     */
    public function getStage(): string
    {
        return Stage::AFTER_RELEASE;
    }
}
