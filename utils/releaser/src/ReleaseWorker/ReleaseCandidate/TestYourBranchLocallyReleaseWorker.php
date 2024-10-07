<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;
use Symfony\Component\Process\Exception\ProcessFailedException;

final class TestYourBranchLocallyReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     * @return string
     */
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return 'Test your branch locally - running composer-dev, standards and tests - this might take a few minutes';
    }

    /**
     * @param \PharIo\Version\Version $version
     * @param string $initialBranchName
     */
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        try {
            $output = $this->processRunner->run('php phing composer-dev standards tests');

            $this->symfonyStyle->writeln(trim($output));
        } catch (ProcessFailedException $ex) {
            $this->symfonyStyle->caution($ex->getProcess()->getOutput());
            $this->symfonyStyle->note('A problem occurred, check the output and fix it please.');
            $runChecksAgain = $this->symfonyStyle->ask('Run the checks again?', 'yes');

            if ($runChecksAgain === 'yes') {
                $this->work($version);
            }
        }
        $this->success();
    }

    /**
     * @return string[]
     */
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE_CANDIDATE];
    }
}
