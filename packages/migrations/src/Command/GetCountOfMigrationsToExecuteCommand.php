<?php

namespace Shopsys\MigrationBundle\Command;

use Doctrine\Bundle\MigrationsBundle\Command\DoctrineCommand;
use Doctrine\DBAL\Migrations\Version;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetCountOfMigrationsToExecuteCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:migrations:count';

    protected function configure()
    {
        $this
            ->setDescription('Get count of migrations to execute.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrationsConfiguration = $this->getMigrationsConfiguration();

        /** @var \Symfony\Bundle\FrameworkBundle\Console\Application $application */
        $application = $this->getApplication();
        DoctrineCommand::configureMigrations($application->getKernel()->getContainer(), $migrationsConfiguration);

        $latestVersion = $migrationsConfiguration->getLatestVersion();
        $migrationsToExecute = $migrationsConfiguration->getMigrationsToExecute(Version::DIRECTION_UP, $latestVersion);

        $output->writeln('Count of migrations to execute: ' . count($migrationsToExecute));

        return 0;
    }
}
