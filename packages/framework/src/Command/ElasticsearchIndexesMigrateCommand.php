<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Model\Elasticsearch\IndexFacade;
use Shopsys\FrameworkBundle\Model\Elasticsearch\IndexRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ElasticsearchIndexesMigrateCommand extends Command
{
    private const ARGUMENT_DOCUMENT_NAME = 'name';

    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:elasticsearch:indexes-migrate';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexRegistry
     */
    protected $indexRegistry;

    /** @var \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexFacade */
    protected $indexFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexRegistry $indexRegistry
     * @param \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexFacade $indexFacade
     */
    public function __construct(IndexRegistry $indexRegistry, IndexFacade $indexFacade)
    {
        $this->indexRegistry = $indexRegistry;
        $this->indexFacade = $indexFacade;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument(
                self::ARGUMENT_DOCUMENT_NAME,
                InputArgument::OPTIONAL,
                sprintf(
                    'Which index will be migrated? Available indexes: "%s"',
                    implode(', ', $this->indexRegistry->getRegisteredIndexNames())
                )
            )
            ->setDescription('Migrate indexes in Elasticsearch');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $symfonyStyleIo = new SymfonyStyle($input, $output);
        $documentName = $input->getArgument(self::ARGUMENT_DOCUMENT_NAME);
        $output->writeln('Migrating index');

        if ($documentName) {
            $this->indexFacade->migrateIndex($this->indexRegistry->getIndexByIndexName($documentName), $output);
        } else {
            $this->indexFacade->migrateByIndexes($this->indexRegistry->getRegisteredIndexes(), $output);
        }

        $symfonyStyleIo->success('Index migrated successfully!');
    }
}
