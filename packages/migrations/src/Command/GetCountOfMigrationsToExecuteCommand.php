<?php

namespace Shopsys\MigrationBundle\Command;

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Version\AliasResolver;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationLockPlanCalculator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetCountOfMigrationsToExecuteCommand extends Command
{
    protected const OPTION_SIMPLE = 'simple';

    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnlySniff.ReferenceViaFullyQualifiedName
     */
    protected static $defaultName = 'shopsys:migrations:count';

    /**
     * @var \Doctrine\Migrations\Version\AliasResolver
     */
    protected AliasResolver $aliasResolver;

    /**
     * @var \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationLockPlanCalculator
     */
    protected MigrationLockPlanCalculator $migrationLockPlanCalculator;

    /**
     * @param \Doctrine\Migrations\DependencyFactory $dependencyFactory
     * @param \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationLockPlanCalculator $migrationLockPlanCalculator
     */
    public function __construct(DependencyFactory $dependencyFactory, MigrationLockPlanCalculator $migrationLockPlanCalculator)
    {
        parent::__construct();

        $this->aliasResolver = $dependencyFactory->getVersionAliasResolver();
        $this->migrationLockPlanCalculator = $migrationLockPlanCalculator;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Get count of migrations to execute.')
            ->addOption(
                static::OPTION_SIMPLE,
                null,
                InputOption::VALUE_NONE,
                'Return only count of migrations to execute.',
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $latestVersion = $this->aliasResolver->resolveVersionAlias('latest');
        $migrationPlanList = $this->migrationLockPlanCalculator->getPlanUntilVersion($latestVersion);

        if ($input->getOption(static::OPTION_SIMPLE)) {
            $output->writeln((string)$migrationPlanList->count());
        } else {
            $output->writeln('Count of migrations to execute: ' . $migrationPlanList->count());
        }

        return Command::SUCCESS;
    }
}
