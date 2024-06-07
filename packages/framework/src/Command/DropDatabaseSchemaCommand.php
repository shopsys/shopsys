<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Doctrine\DatabaseSchemaFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shopsys:schema:drop',
    description: 'Drop database public schema',
)]
class DropDatabaseSchemaCommand extends Command
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\DatabaseSchemaFacade $databaseSchemaFacade
     */
    public function __construct(private readonly DatabaseSchemaFacade $databaseSchemaFacade)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'tables-only',
                null,
                InputOption::VALUE_NONE,
                'Drop tables, but preserve schema',
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('tables-only')) {
            $output->writeln('Dropping tables in the public schema...');
            $this->databaseSchemaFacade->dropTables();
            $output->writeln('Tables dropped successfully!');

            return Command::SUCCESS;
        }

        $output->writeln('Dropping database schema...');
        $this->databaseSchemaFacade->dropSchemaIfExists('public');
        $output->writeln('Database schema dropped successfully!');

        return Command::SUCCESS;
    }
}
