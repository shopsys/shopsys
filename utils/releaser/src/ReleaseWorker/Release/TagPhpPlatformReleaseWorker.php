<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\Release;

use PharIo\Version\Version;
use Shopsys\Releaser\FileManipulator\DockerfileVersionFileManipulator;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Message;
use Shopsys\Releaser\Stage;

final class TagPhpPlatformReleaseWorker extends AbstractShopsysReleaseWorker
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
        return 'Tag php-platform with the same version as the release and replace FROM in Dockerfile';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $tempDirectory = trim($this->processRunner->run('mktemp -d -t shopsys-release-XXXX'));
        $packageName = 'php-platform';
        $versionString = $version->getOriginalString();

        $this->symfonyStyle->note(sprintf('Cloning shopsys/%s. This can take a while.', $packageName));
        $this->processRunner->run(
            sprintf('cd %s && git clone https://github.com/shopsys/%s.git', $tempDirectory, $packageName),
        );

        $this->processRunner->run(
            sprintf(
                'cd %s/%s && git checkout %s && git tag %s',
                $tempDirectory,
                $packageName,
                $initialBranchName,
                $versionString,
            ),
        );

        $output = $this->processRunner->run(
            sprintf(
                'cd %s/%s && git log --graph --oneline --decorate=short --color | head',
                $tempDirectory,
                $packageName,
            ),
        );

        $this->symfonyStyle->writeln(trim($output));

        $isTaggedProperly = $this->symfonyStyle->ask(
            sprintf('Package shopsys/%s: Is the tag on right commit and should be pushed?', $packageName),
            'yes',
        );

        if (!$isTaggedProperly) {
            $this->confirm(
                sprintf('Please fix the problem in shopsys/%s and split the monorepo again. This step will be repeated after you confirm.', $packageName),
            );
            $this->processRunner->run('rm -r ' . $tempDirectory);
            $this->work($version);

            return;
        }

        $this->processRunner->run(
            sprintf('cd %s/%s && git push origin %s', $tempDirectory, $packageName, $versionString),
        );


        $this->processRunner->run('rm -r ' . $tempDirectory);
        $this->symfonyStyle->note([
            sprintf('Wait for Github Actions to build a tagged version of %s (approx 1 hour)', $packageName),
            sprintf('You can track progress on https://github.com/shopsys/%s/actions', $packageName),
        ]);
        $this->confirm(
            sprintf('Confirm that there are new version of %s on Docker Hub (https://hub.docker.com/r/shopsys/%1$s/tags)', $packageName),
        );

        $this->dockerfileVersionFileManipulator->updateDockerFileVersion($versionString);

        $infoMessage = sprintf(
            '%s base image version in Dockerfile set to "%s"',
            $packageName,
            $versionString,
        );
        $this->symfonyStyle->note($infoMessage);

        $this->commit($infoMessage);

        $this->symfonyStyle->success(Message::SUCCESS);
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE;
    }
}
