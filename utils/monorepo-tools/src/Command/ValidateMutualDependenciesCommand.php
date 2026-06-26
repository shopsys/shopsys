<?php

declare(strict_types=1);

namespace Shopsys\MonorepoTools\Command;

use Override;
use Shopsys\MonorepoTools\MutualDependency\MutualDependencyChecker;
use Shopsys\Releaser\FileManipulator\ComposerJsonFileManipulator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'monorepo:validate-mutual-dependencies',
    description: 'Validate that every package directly requires all of its transitive dev-version shopsys dependencies.',
)]
final class ValidateMutualDependenciesCommand extends Command
{
    private const string OPTION_FIX = 'fix';

    public function __construct(
        private readonly MutualDependencyChecker $mutualDependencyChecker,
        private readonly ComposerJsonFileManipulator $composerJsonFileManipulator,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this->addOption(
            self::OPTION_FIX,
            null,
            InputOption::VALUE_NONE,
            'Add the missing requires into the composer.json files instead of only reporting them',
        );
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $missingMutualDependencies = $this->mutualDependencyChecker->check();

        if ($missingMutualDependencies === []) {
            $symfonyStyle->success(
                'All packages directly require their transitive dev-version shopsys dependencies.',
            );

            return Command::SUCCESS;
        }

        $isFix = (bool)$input->getOption(self::OPTION_FIX);

        foreach ($missingMutualDependencies as $missingMutualDependency) {
            $symfonyStyle->section($missingMutualDependency->packageName);

            $missingRequires = [];

            foreach ($missingMutualDependency->missingRequiresByVersion as $packageName => $versionConstraint) {
                $missingRequires[] = sprintf('"%s": "%s"', $packageName, $versionConstraint);
            }

            $symfonyStyle->listing($missingRequires);

            if ($isFix) {
                $this->composerJsonFileManipulator->addRequires(
                    $missingMutualDependency->composerJsonFileInfo,
                    $missingMutualDependency->missingRequiresByVersion,
                );
            }
        }

        if ($isFix) {
            $symfonyStyle->success(
                sprintf('Added missing requires into %d package(s).', count($missingMutualDependencies)),
            );

            return Command::SUCCESS;
        }

        $symfonyStyle->error(sprintf(
            '%d package(s) do not directly require all of their transitive dev-version shopsys dependencies.'
                . ' Run the command with --fix to add them.',
            count($missingMutualDependencies),
        ));

        return Command::FAILURE;
    }
}
