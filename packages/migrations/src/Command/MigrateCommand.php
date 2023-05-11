<?php

namespace Shopsys\MigrationBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Shopsys\MigrationBundle\Command\Exception\CheckSchemaCommandException;
use Shopsys\MigrationBundle\Command\Exception\MigrateCommandException;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationLockPlanCalculator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLock;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command
{
    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnlySniff.ReferenceViaFullyQualifiedName
     */
    protected static $defaultName = 'shopsys:migrations:migrate';

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * @var \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLock
     */
    protected MigrationsLock $migrationsLock;

    /**
     * @var \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationLockPlanCalculator
     */
    protected MigrationLockPlanCalculator $migrationLockPlanCalculator;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLock $migrationsLock
     * @param \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationLockPlanCalculator $migrationLockPlanCalculator
     */
    public function __construct(
        EntityManagerInterface $em,
        MigrationsLock $migrationsLock,
        MigrationLockPlanCalculator $migrationLockPlanCalculator
    ) {
        $this->em = $em;
        $this->migrationsLock = $migrationsLock;
        $this->migrationLockPlanCalculator = $migrationLockPlanCalculator;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(
                'Execute all database migrations and check if database schema is satisfying ORM, all in one transaction.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->em->wrapInTransaction(function () use ($output) {
                $this->executeDoctrineMigrateCommand($output);

                $output->writeln('');

                $this->executeCheckSchemaCommand($output);
            });
        } catch (Exception $ex) {
            $message = 'Database migration process did not run properly. Transaction was reverted.';
            throw new MigrateCommandException($message, $ex);
        }

        $availableMigrationsList = $this->migrationLockPlanCalculator->getMigrations();
        $this->migrationsLock->saveNewMigrations($availableMigrationsList);

        return 0;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
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
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
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
