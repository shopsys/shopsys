<?php

declare(strict_types=1);

namespace Shopsys\Cli\Command;

use Exception;
use Override;
use Shopsys\Cli\Exception\CancelledException;
use Shopsys\Cli\Input\InteractiveInputCollector;
use Shopsys\Cli\Input\YamlConfigLoader;
use Shopsys\Cli\Worker\WorkerInterface;
use Shopsys\Cli\Worker\WorkerResult;
use Shopsys\Cli\Worker\WorkerRunner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'configure',
    description: 'Configure an existing Shopsys Platform project',
)]
class ConfigureCommand extends Command
{
    private const string OPTION_CONFIG = 'config';
    private const string OPTION_MODIFICATIONS = 'modifications';
    private const string ARGUMENT_PATH = 'path';

    public function __construct(
        private readonly WorkerRunner $workerRunner,
        private readonly InteractiveInputCollector $interactiveInputCollector,
        private readonly YamlConfigLoader $yamlConfigLoader,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $optionConfig = self::OPTION_CONFIG;
        $optionModifications = self::OPTION_MODIFICATIONS;

        $this
            ->addArgument(
                self::ARGUMENT_PATH,
                InputArgument::REQUIRED,
                'Path to the project directory',
            )
            ->addOption(
                $optionConfig,
                $optionConfig[0],
                InputOption::VALUE_REQUIRED,
                'Path to YAML configuration file (for non-interactive mode)',
            )
            ->addOption(
                $optionModifications,
                $optionModifications[0],
                InputOption::VALUE_NONE,
                'Display modified files',
            )
            ->setHelp(
                <<<HELP
The <info>%command.name%</info> command configures an existing Shopsys Platform project.

<info>Interactive mode:</info>
    <comment>%command.full_name% /path/to/project</comment>

<info>Non-interactive mode (from YAML file):</info>
    <comment>%command.full_name% --{$optionConfig}=project-config.yaml</comment>

<info>Show modified files for each step:</info>
    <comment>%command.full_name% --{$optionModifications}</comment>
HELP,
            );
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projectPath = realpath($input->getArgument(self::ARGUMENT_PATH));

        if ($projectPath === false) {
            $io->error(sprintf('Project path not found: %s', $input->getArgument(self::ARGUMENT_PATH)));

            return Command::FAILURE;
        }

        try {
            $configFile = $input->getOption(self::OPTION_CONFIG);

            if ($configFile !== null) {
                $config = $this->yamlConfigLoader->load($configFile, $projectPath);
            } else {
                $config = $this->interactiveInputCollector->collect($projectPath, $io);
            }
        } catch (CancelledException) {
            $io->warning('Configuration cancelled by user.');
            $io->block(
                sprintf('Copy the configuration printed above into a YAML file, fix it, and rerun with the --%s option', self::OPTION_CONFIG),
                'TIP',
                'fg=bright-cyan',
            );

            return Command::SUCCESS;
        } catch (Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->title('Applying Configuration');

        $results = $this->workerRunner->run(
            $config,
            $projectPath,
            function (WorkerInterface $worker, WorkerResult $result) use ($io, $input): void {
                if ($result->success) {
                    $io->section(sprintf('<fg=green>✓</> %s', $result->message));

                    if ($input->getOption(self::OPTION_MODIFICATIONS)) {
                        if (count($result->filesModified) > 0) {
                            $io->writeln('✏️ <fg=green>Modified files:</>');
                            $io->listing($result->filesModified);
                        }

                        if (count($result->filesCreated) > 0) {
                            $io->writeln('️➕ <fg=green>Created files:</>');
                            $io->listing($result->filesCreated);
                        }

                        if (count($result->filesDeleted) > 0) {
                            $io->writeln('️🗑️ <fg=green>Deleted files:</>');
                            $io->listing($result->filesDeleted);
                        }
                    }
                } else {
                    $io->writeln(sprintf('<fg=red>✗</> %s: %s', $worker->getName(), $result->message));
                }
            },
        );

        $io->newLine();

        if ($this->workerRunner->allSuccessful($results)) {
            $io->success('Configuration complete!');
            $this->displayHints($io, $results);
            $this->displayNextSteps($io);

            return Command::SUCCESS;
        }

        $io->warning('Configuration completed with some errors. Please review the output above.');

        return Command::FAILURE;
    }

    /**
     * @param array<\Shopsys\Cli\Worker\WorkerResult> $results
     */
    private function displayHints(SymfonyStyle $io, array $results): void
    {
        $hints = $this->workerRunner->collectHints($results);

        if (count($hints) === 0) {
            return;
        }

        $io->writeln('<info>Important notes:</info>');
        $io->listing($hints);
    }

    private function displayNextSteps(SymfonyStyle $io): void
    {
        $io->writeln('<info>Next steps:</info>');
        $io->listing([
            'Install project: <comment>./scripts/install.sh</comment>',
            'Fix backend standards: <comment>docker compose exec php-fpm php phing standards-fix</comment>',
            'Dump backend translations: <comment>docker compose exec php-fpm php phing translations-dump</comment>',
            'Fix storefront standards: <comment>docker compose exec storefront pnpm run check--fix</comment>',
            'Dump storefront translations: <comment>docker compose exec storefront pnpm run translate</comment>',
            'Verify and commit changes to git',
        ]);
    }
}
