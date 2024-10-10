<?php

declare(strict_types=1);

namespace Shopsys\Releaser\Command;

use InvalidArgumentException;
use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\ReleaseWorkerProvider;
use Shopsys\Releaser\ReleaseWorker\StageWorkerInterface;
use Shopsys\Releaser\Stage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'monorepo:release',
    description: 'Perform release process with set Release Workers.',
)]
final class ReleaseCommand extends Command
{
    private const string RESUME_STEP = 'resume-step';
    private const string INITIAL_BRANCH_NAME = 'initial-branch';
    private const string VERSION = 'version';
    private const string DRY_RUN = 'dry-run';
    private const string STAGE = 'stage';

    /**
     * @param \Shopsys\Releaser\ReleaseWorker\ReleaseWorkerProvider $releaseWorkerProvider
     * @param \Shopsys\Releaser\Command\SymfonyStyleFactory $symfonyStyleFactory
     */
    public function __construct(
        private readonly ReleaseWorkerProvider $releaseWorkerProvider,
        private readonly SymfonyStyleFactory $symfonyStyleFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $description = sprintf(
            'Release version, in format "<major>.<minor>.<patch>" or "v<major>.<minor>.<patch> or one of keywords: "%s"',
            implode('", "', ['major', 'minor', 'patch']),
        );
        $this->addArgument(self::VERSION, InputArgument::REQUIRED, $description);

        $this->addOption(
            self::DRY_RUN,
            null,
            InputOption::VALUE_NONE,
            'Do not perform operations, just their preview',
        );

        $this->addOption(self::STAGE, null, InputOption::VALUE_REQUIRED, 'Name of stage to perform');
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
        $symfonyStyle = $this->symfonyStyleFactory->createAndStoreSymfonyStyle($input, $output);

        // validation phase
        $stage = $this->resolveStage($input);
        $step = $this->resolveStep($input);
        $initialBranchName = $this->resolveInitialBranchName($input);

        $this->checkStage($stage);

        /** @var string $versionArgument */
        $versionArgument = $input->getArgument(self::VERSION);

        $version = new Version($versionArgument);

        $activeReleaseWorkers = $this->releaseWorkerProvider->provideByStage($stage, $step);

        $totalWorkerCount = count($activeReleaseWorkers) + $step;
        $isDryRun = (bool)$input->getOption(self::DRY_RUN);

        foreach ($activeReleaseWorkers as $releaseWorker) {
            $title = sprintf('%d/%d) %s', ++$step, $totalWorkerCount, $releaseWorker->getDescription($version, $initialBranchName));
            $symfonyStyle->title($title);
            $this->printReleaseWorkerMetadata($releaseWorker, $symfonyStyle);

            if (!$isDryRun) {
                $releaseWorker->work($version, $initialBranchName);
            }
        }

        if ($isDryRun) {
            $symfonyStyle->note('Running in dry mode, nothing is changed');
        } elseif ($stage === null) {
            $symfonyStyle->success(sprintf('Version "%s" is now released!', $version->getVersionString()));
        } else {
            $symfonyStyle->success(
                sprintf('Stage "%s" for version "%s" is now finished!', $stage, $version->getVersionString()),
            );
        }

        return Command::SUCCESS;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return string|null
     */
    private function resolveStage(InputInterface $input): ?string
    {
        $stage = $input->getOption(self::STAGE);

        return $stage !== null ? (string)$stage : $stage;
    }

    /**
     * @param string|null $stage
     */
    private function checkStage(?string $stage): void
    {
        if (in_array($stage, Stage::getAllStages(), true)) {
            return;
        }

        throw new InvalidArgumentException(sprintf(
            'Stage "%s" was not found. Pick one of: "%s"',
            $stage,
            implode('", "', Stage::getAllStages()),
        ));
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
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle
     */
    private function printReleaseWorkerMetadata(
        StageWorkerInterface $releaseWorker,
        SymfonyStyle $symfonyStyle,
    ): void {
        if (!$symfonyStyle->isVerbose()) {
            return;
        }

        // show class on -v/--verbose/--debug
        $symfonyStyle->writeln('class: ' . get_class($releaseWorker));
        $symfonyStyle->newLine();
    }
}
