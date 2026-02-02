<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Override;
use Shopsys\FrameworkBundle\Component\Doctrine\DatabaseSchemaFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shopsys:schema:import-default',
    description: 'Import database default schema',
)]
class ImportDefaultDatabaseSchemaCommand extends Command
{
    public function __construct(private readonly DatabaseSchemaFacade $databaseSchemaFacade)
    {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Importing default database schema...');
        $this->databaseSchemaFacade->importDefaultSchema();
        $output->writeln('Default database schema imported successfully!');

        return Command::SUCCESS;
    }
}
