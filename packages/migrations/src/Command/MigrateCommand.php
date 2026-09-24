<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Command;

use Doctrine\Migrations\Metadata\AvailableMigrationsList;
use Doctrine\Migrations\Metadata\MigrationPlan;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Override;
use Shopsys\MigrationBundle\Command\Exception\CheckSchemaCommandException;
use Shopsys\MigrationBundle\Command\Exception\MigrateCommandException;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationLockPlanCalculator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLock;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'shopsys:migrations:migrate',
    description: 'Execute all database migrations and check if database schema is satisfying ORM, all in one transaction',
)]
class MigrateCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly MigrationsLock $migrationsLock,
        protected readonly MigrationLockPlanCalculator $migrationLockPlanCalculator,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $availableMigrationsList = $this->migrationLockPlanCalculator->getMigrations();
        $migrationPlans = $this->getMigrationPlansUntilLatestVersion($availableMigrationsList);

        try {
            $this->em->wrapInTransaction(function () use ($output): void {
                $this->executeDoctrineMigrateCommand($output);

                $output->writeln('');

                $this->executeCheckSchemaCommand($output);
            });
        } catch (Exception $ex) {
            $message = 'Database migration process did not run properly. Transaction was reverted.';

            throw new MigrateCommandException($message, $ex);
        }

        $this->writeExecutedMigrations($migrationPlans, $availableMigrationsList, new SymfonyStyle($input, $output));
        $this->migrationsLock->saveNewMigrations($availableMigrationsList);

        return Command::SUCCESS;
    }

    protected function executeDoctrineMigrateCommand(OutputInterface $output): void
    {
        $doctrineMigrateCommand = $this->getApplication()->find('doctrine:migrations:migrate');
        $arguments = [
            'command' => 'doctrine:migrations:migrate',
            '--allow-no-migration' => true,
        ];

        $input = new ArrayInput($arguments);
        $input->setInteractive(false);

        $exitCode = $doctrineMigrateCommand->run($input, $output);

        if ($exitCode !== 0) {
            $message = 'Doctrine migration command did not exit properly (exit code is ' . $exitCode . ').';

            throw new MigrateCommandException($message);
        }
    }

    /**
     * @return \Doctrine\Migrations\Metadata\MigrationPlan[]
     */
    protected function getMigrationPlansUntilLatestVersion(AvailableMigrationsList $availableMigrationsList): array
    {
        if (count($availableMigrationsList) === 0) {
            return [];
        }

        return $this->migrationLockPlanCalculator
            ->getPlanUntilVersion($availableMigrationsList->getLast()->getVersion())
            ->getItems();
    }

    /**
     * Doctrine reports only the target version,
     * so the migrations executed in the committed transaction are listed explicitly
     *
     * @param \Doctrine\Migrations\Metadata\MigrationPlan[] $migrationPlans
     */
    protected function writeExecutedMigrations(
        array $migrationPlans,
        AvailableMigrationsList $availableMigrationsList,
        SymfonyStyle $io,
    ): void {
        $migrationsCount = count($migrationPlans);

        if ($migrationsCount === 0) {
            return;
        }

        if ($migrationsCount === count($availableMigrationsList)) {
            // all available migrations were executed (e.g. on a fresh database), listing them would only flood the output
            $io->text(sprintf('Executed all %d available migrations.', $migrationsCount));

            return;
        }

        $io->text($migrationsCount === 1
            ? 'Executed 1 migration:'
            : sprintf('Executed %d migrations in this order:', $migrationsCount));
        $io->listing(array_map(
            static fn (MigrationPlan $migrationPlan): string => (string)$migrationPlan->getVersion(),
            $migrationPlans,
        ));
    }

    protected function executeCheckSchemaCommand(OutputInterface $output): void
    {
        $checkSchemaCommand = $this->getApplication()->find('shopsys:migrations:check-schema');
        $arguments = [
            'command' => 'shopsys:migrations:check-schema',
        ];
        $input = new ArrayInput($arguments);
        $input->setInteractive(false);

        $exitCode = $checkSchemaCommand->run($input, $output);

        if ($exitCode !== 0) {
            $message = 'Database schema check did not exit properly (exit code is ' . $exitCode . ').';

            throw new CheckSchemaCommandException($message);
        }
    }
}
