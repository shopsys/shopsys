<?php

declare(strict_types=1);


namespace App\Command;

use App\Component\Router\Import\SingleImportUrlRedirectTableFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SingleImportUrlRedirectTableCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'sconto:import:redirecttable';

    /**
     * @var \App\Component\Router\Import\SingleImportUrlRedirectTableFacade
     */
    private SingleImportUrlRedirectTableFacade $singleImportUrlRedirectTableFacade;

    /**
     * @param \App\Component\Router\Import\SingleImportUrlRedirectTableFacade $singleImportUrlRedirectTableFacade
     */
    public function __construct(SingleImportUrlRedirectTableFacade $singleImportUrlRedirectTableFacade)
    {
        parent::__construct();
        $this->singleImportUrlRedirectTableFacade = $singleImportUrlRedirectTableFacade;
    }

    protected function configure()
    {
        $this->setDescription('Single import url redirect table, ec: php bin/console sconto:import:redirecttable -f /web/public/importFiles/ScontoRedirectUrlTable.csv');
        $this->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'FilePath to url redirect url CSV file. (exam: /web/public/importFiles/ScontoRedirectUrlTable.csv)');
        $this->addOption('domain', 'd', InputOption::VALUE_REQUIRED, 'Available values are cz/sk', 'cz');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->singleImportUrlRedirectTableFacade->runTransfer($input->getOption('file'), $input->getOption('domain'));

        return 0;
    }
}
