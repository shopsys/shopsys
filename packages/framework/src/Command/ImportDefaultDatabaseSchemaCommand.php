<?php

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Doctrine\DatabaseSchemaFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportDefaultDatabaseSchemaCommand extends Command
{

    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:schema:import-default';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\DatabaseSchemaFacade
     */
    private $databaseSchemaFacade;

    public function __construct(DatabaseSchemaFacade $databaseSchemaFacade)
    {
        $this->databaseSchemaFacade = $databaseSchemaFacade;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Import database default schema');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Importing default database schema...');
        $this->databaseSchemaFacade->importDefaultSchema();
        $output->writeln('Default database schema imported successfully!');
    }
}
