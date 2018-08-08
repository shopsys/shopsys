<?php

namespace Shopsys\MigrationBundle\Command;

use Doctrine\DBAL\Migrations\Version;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetCountOfMigrationsToExecuteCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:migrations:count';

    protected function configure(): void
    {
        $this
            ->setDescription('Get count of migrations to execute.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $migrationsConfiguration = $this->getMigrationsConfiguration();

        $latestVersion = $migrationsConfiguration->getLatestVersion();
        $migrationsToExecute = $migrationsConfiguration->getMigrationsToExecute(Version::DIRECTION_UP, $latestVersion);

        $output->writeln('Count of migrations to execute: ' . count($migrationsToExecute));
    }
}
