<?php

declare(strict_types=1);

namespace Shopsys\Releaser\Command;

use InvalidArgumentException;
use Shopsys\Releaser\ReleaseWorker\ReleaseWorkerProvider;
use Shopsys\Releaser\ReleaseWorker\StageWorkerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\MonorepoBuilder\Release\Guard\ReleaseGuard;
use Symplify\MonorepoBuilder\Release\ValueObject\SemVersion;
use Symplify\MonorepoBuilder\Release\Version\VersionFactory;
use Symplify\MonorepoBuilder\ValueObject\Option;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class ReleaseCommand extends Command
{
    private const RESUME_STEP = 'resume-step';
    private const INITIAL_BRANCH_NAME = 'initial-branch';

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle
     * @param \Shopsys\Releaser\ReleaseWorker\ReleaseWorkerProvider $releaseWorkerProvider
     * @param \Symplify\MonorepoBuilder\Release\Guard\ReleaseGuard $releaseGuard
     * @param \Symplify\MonorepoBuilder\Release\Version\VersionFactory $versionFactory
     */
    public function __construct(
        private readonly SymfonyStyle $symfonyStyle,
        private readonly ReleaseWorkerProvider $releaseWorkerProvider,
        private readonly ReleaseGuard $releaseGuard,
        private readonly VersionFactory $versionFactory
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Perform release process with set Release Workers.');

        $description = sprintf(
            'Release version, in format "<major>.<minor>.<patch>" or "v<major>.<minor>.<patch> or one of keywords: "%s"',
            implode('", "', SemVersion::ALL)
        );
        $this->addArgument(Option::VERSION, InputArgument::REQUIRED, $description);

        $this->addOption(
            Option::DRY_RUN,
            null,
            InputOption::VALUE_NONE,
            'Do not perform operations, just their preview'
        );

        $this->addOption(Option::STAGE, null, InputOption::VALUE_REQUIRED, 'Name of stage to perform');
        $this->addOption(self::RESUME_STEP, null, InputOption::VALUE_REQUIRED, 'Number of step to start from');
        $this->addOption(self::INITIAL_BRANCH_NAME, null, InputOption::VALUE_REQUIRED, 'Name of branch you are releasing version on');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // validation phase
        $stage = $this->resolveStage($input);
        $step = $this->resolveStep($input);
        $initialBranchName = $this->resolveInitialBranchName($input);

        $this->releaseGuard->guardStage($stage);

        /** @var string $versionArgument */
        $versionArgument = $input->getArgument(Option::VERSION);
        $version = $this->versionFactory->createValidVersion($versionArgument, $stage);

        $activeReleaseWorkers = $this->releaseWorkerProvider->provideByStage($stage, $step);

        $totalWorkerCount = count($activeReleaseWorkers) + $step;
        $isDryRun = (bool)$input->getOption(Option::DRY_RUN);

        foreach ($activeReleaseWorkers as $releaseWorker) {
            $title = sprintf('%d/%d) %s', ++$step, $totalWorkerCount, $releaseWorker->getDescription($version, $initialBranchName));
            $this->symfonyStyle->title($title);
            $this->printReleaseWorkerMetadata($releaseWorker);

            if (!$isDryRun) {
                $releaseWorker->work($version, $initialBranchName);
            }
        }

        if ($isDryRun) {
            $this->symfonyStyle->note('Running in dry mode, nothing is changed');
        } elseif ($stage === null) {
            $this->symfonyStyle->success(sprintf('Version "%s" is now released!', $version->getVersionString()));
        } else {
            $this->symfonyStyle->success(
                sprintf('Stage "%s" for version "%s" is now finished!', $stage, $version->getVersionString())
            );
        }

        return ShellCode::SUCCESS;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return string|null
     */
    private function resolveStage(InputInterface $input): ?string
    {
        $stage = $input->getOption(Option::STAGE);

        return $stage !== null ? (string)$stage : $stage;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return int
     */
    private function resolveStep(InputInterface $input): int
    {
        $step = $input->getOption(self::RESUME_STEP);

        return $step !== null ? (int)$step - 1 : 0;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return string
     */
    private function resolveInitialBranchName(InputInterface $input): string
    {
        $initialBranchName = $input->getOption(self::INITIAL_BRANCH_NAME);

        if ($initialBranchName === null) {
            throw new InvalidArgumentException('Initial branch name must be provided.');
        }

        return (string)$initialBranchName;
    }

    /**
     * @param \Shopsys\Releaser\ReleaseWorker\StageWorkerInterface $releaseWorker
     */
    private function printReleaseWorkerMetadata(StageWorkerInterface $releaseWorker): void
    {
        if (!$this->symfonyStyle->isVerbose()) {
            return;
        }

        // show class on -v/--verbose/--debug
        $this->symfonyStyle->writeln('class: ' . get_class($releaseWorker));
        $this->symfonyStyle->newLine();
    }
}
