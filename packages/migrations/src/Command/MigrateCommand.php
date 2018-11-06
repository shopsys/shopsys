<?php

namespace Shopsys\MigrationBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLock;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends AbstractCommand
{
    const RETURN_CODE_OK = 0;
    const RETURN_CODE_ERROR = 1;

    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:migrations:migrate';

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLock
     */
    private $migrationsLock;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLock $migrationsLock
     */
    public function __construct(
        EntityManagerInterface $em,
        MigrationsLock $migrationsLock
    ) {
        $this->em = $em;
        $this->migrationsLock = $migrationsLock;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription(
                'Execute all database migrations and check if database schema is satisfying ORM, all in one transaction.'
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->em->transactional(function () use ($output) {
                $this->executeDoctrineMigrateCommand($output);

                $output->writeln('');

                $this->executeCheckSchemaCommand($output);
            });
        } catch (\Exception $ex) {
            $message = 'Database migration process did not run properly. Transaction was reverted.';
            throw new \Shopsys\MigrationBundle\Command\Exception\MigrateCommandException($message, $ex);
        }

        $migrationVersions = $this->getMigrationsConfiguration()->getMigrations();
        $this->migrationsLock->saveNewMigrations($migrationVersions);
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function executeDoctrineMigrateCommand(OutputInterface $output)
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
            throw new \Shopsys\MigrationBundle\Command\Exception\MigrateCommandException($message);
        }
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function executeCheckSchemaCommand(OutputInterface $output)
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
            throw new \Shopsys\MigrationBundle\Command\Exception\CheckSchemaCommandException($message);
        }
    }
}
