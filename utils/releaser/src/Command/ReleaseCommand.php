<?php

declare(strict_types=1);

namespace Shopsys\Releaser\Command;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\ReleaseWorkerProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\MonorepoBuilder\Configuration\Option;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Guard\ReleaseGuard;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class ReleaseCommand extends Command
{
    private const RESUME_STEP = 'resume-step';

    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var \Symplify\MonorepoBuilder\Release\Guard\ReleaseGuard
     */
    private $releaseGuard;

    /**
     * @var \Shopsys\Releaser\ReleaseWorker\ReleaseWorkerProvider
     */
    private $releaseWorkerProvider;

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle
     * @param \Shopsys\Releaser\ReleaseWorker\ReleaseWorkerProvider $releaseWorkerProvider
     * @param \Symplify\MonorepoBuilder\Release\Guard\ReleaseGuard $releaseGuard
     */
    public function __construct(
        SymfonyStyle $symfonyStyle,
        ReleaseWorkerProvider $releaseWorkerProvider,
        ReleaseGuard $releaseGuard
    ) {
        parent::__construct();

        $this->symfonyStyle = $symfonyStyle;
        $this->releaseGuard = $releaseGuard;
        $this->releaseWorkerProvider = $releaseWorkerProvider;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Perform release process with set Release Workers.');

        $this->addArgument(
            Option::VERSION,
            InputArgument::REQUIRED,
            'Release version, in format "<major>.<minor>.<patch>" or "v<major>.<minor>.<patch>"'
        );

        $this->addOption(
            Option::DRY_RUN,
            null,
            InputOption::VALUE_NONE,
            'Do not perform operations, just their preview'
        );

        $this->addOption(Option::STAGE, null, InputOption::VALUE_REQUIRED, 'Name of stage to perform');
        $this->addOption(self::RESUME_STEP, null, InputOption::VALUE_REQUIRED, 'Number of step to start from');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // validation phase
        $stage = $input->getOption(Option::STAGE);
        $stage = $stage !== null ? (string)$stage : $stage;
        $step = $this->resolveStep($input);

        $this->releaseGuard->guardStage($stage);

        /** @var string $versionArgument */
        $versionArgument = $input->getArgument(Option::VERSION);
        $version = $this->createValidVersion($versionArgument, $stage);

        $activeReleaseWorkers = $this->releaseWorkerProvider->provideByStage($stage, $step);

        $totalWorkerCount = count($activeReleaseWorkers) + $step;
        $isDryRun = (bool)$input->getOption(Option::DRY_RUN);

        foreach ($activeReleaseWorkers as $releaseWorker) {
            $title = sprintf('%d/%d) %s', ++$step, $totalWorkerCount, $releaseWorker->getDescription($version));
            $this->symfonyStyle->title($title);
            $this->printReleaseWorkerMetadata($releaseWorker);

            if ($isDryRun === false) {
                $releaseWorker->work($version);
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
     * @param string $versionArgument
     * @param string|null $stage
     * @return \PharIo\Version\Version
     */
    private function createValidVersion(string $versionArgument, ?string $stage): Version
    {
        // this object performs validation of version
        $version = new Version($versionArgument);

        $this->releaseGuard->guardVersion($version, $stage);

        return $version;
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
     * @param \Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface $releaseWorker
     */
    private function printReleaseWorkerMetadata(ReleaseWorkerInterface $releaseWorker): void
    {
        if ($this->symfonyStyle->isVerbose() === false) {
            return;
        }

        // show priority and class on -v/--verbose/--debug
        $this->symfonyStyle->writeln('priority: ' . $releaseWorker->getPriority());
        $this->symfonyStyle->writeln('class: ' . get_class($releaseWorker));
        $this->symfonyStyle->newLine();
    }
}
