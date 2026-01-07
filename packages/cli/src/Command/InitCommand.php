<?php

declare(strict_types=1);

namespace Shopsys\Cli\Command;

use Override;
use Shopsys\Cli\Exception\GitException;
use Shopsys\Cli\Model\GitHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'init',
    description: 'Initialize a new Shopsys Platform project',
)]
class InitCommand extends Command
{
    private const string REPOSITORY_URL = 'https://github.com/shopsys/project-base.git';

    private const string OPTION_BRANCH = 'branch';
    private const string OPTION_PROJECT_NAME = 'project-name';
    private const string OPTION_CONFIG = 'config';
    private const string BRANCH_STABLE = 'stable';

    /**
     * @param \Shopsys\Cli\Model\GitHandler $gitHandler
     */
    public function __construct(
        private readonly GitHandler $gitHandler,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $optionBranch = self::OPTION_BRANCH;
        $optionConfig = self::OPTION_CONFIG;

        $this
            ->addArgument(
                self::OPTION_PROJECT_NAME,
                InputArgument::OPTIONAL,
                'Name of the project directory to create',
                'project-base',
            )
            ->addOption(
                $optionBranch,
                $optionBranch[0],
                InputOption::VALUE_REQUIRED,
                'Git branch or tag to clone',
                self::BRANCH_STABLE,
            )
            ->addOption(
                $optionConfig,
                $optionConfig[0],
                InputOption::VALUE_REQUIRED,
                'Path to YAML configuration file (for non-interactive mode)',
            )
            ->setHelp(
                <<<HELP
The <info>%command.name%</info> command creates a new Shopsys Platform project.

<info>Interactive mode:</info>
    <comment>%command.full_name% my-project</comment>

<info>Non-interactive mode (from YAML file):</info>
    <comment>%command.full_name% my-project --{$optionConfig}=project-config.yaml</comment>

<info>Init a specific version (tag or branch):</info>
    <comment>%command.full_name% my-project --{$optionBranch}=18.0.0</comment>

<info>Default behavior:</info>
    If no version is specified, the latest released tag is used.
HELP,
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projectName = (string)$input->getArgument(self::OPTION_PROJECT_NAME);
        $branch = $this->resolveReference((string)$input->getOption(self::OPTION_BRANCH));
        $configFile = $input->getOption(self::OPTION_CONFIG);

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $projectName)) {
            $io->error('Invalid project name. Use only letters, numbers, underscores, and hyphens.');

            return Command::FAILURE;
        }

        if (is_dir($projectName)) {
            $io->error(sprintf('Directory already exists: %s', $projectName));

            return Command::FAILURE;
        }

        $io->title('Shopsys Cli - Project Initialization');
        $io->section('Cloning Shopsys Platform repository');

        $refDescription = (preg_match('/^(v\d+\.\d+\.\d+)$/', $branch)) ? 'version' : 'branch';
        $io->writeln(sprintf('Cloning %s <info>%s</info>...', $refDescription, $branch));

        try {
            $this->gitHandler->cloneRepository(
                self::REPOSITORY_URL,
                $projectName,
                $branch,
                function ($type, $buffer) use ($io) {
                    $io->write($buffer);
                },
            );
        } catch (GitException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success('Repository cloned successfully');

        return Command::SUCCESS;
    }

    /**
     * @param string $branch
     * @return string
     */
    private function resolveReference(string $branch): string
    {
        if ($branch === self::BRANCH_STABLE) {
            return $this->gitHandler->getLatestTag(self::REPOSITORY_URL);
        }

        if (preg_match('/^\d+\.\d+\.\d+$/', $branch)) {
            $branch = 'v' . $branch;
        }

        return $branch;
    }
}
