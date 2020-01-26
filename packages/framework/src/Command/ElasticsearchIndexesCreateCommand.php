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

class ElasticsearchIndexesCreateCommand extends Command
{
    private const ARGUMENT_INDEX_NAME = 'name';

    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:elasticsearch:indexes-create';

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
                self::ARGUMENT_INDEX_NAME,
                InputArgument::OPTIONAL,
                sprintf(
                    'Which index will be created? Available indexes: "%s"',
                    implode(', ', $this->indexRegistry->getRegisteredIndexNames())
                )
            )
            ->setDescription('Creates structure in Elasticsearch');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $symfonyStyleIo = new SymfonyStyle($input, $output);
        $indexName = $input->getArgument(self::ARGUMENT_INDEX_NAME);
        $output->writeln('Creating structure');

        if ($indexName) {
            $this->indexFacade->createIndex($this->indexRegistry->getIndexByIndexName($indexName), $output);
        } else {
            $this->indexFacade->createIndexes($this->indexRegistry->getRegisteredIndexes(), $output);
        }

        $symfonyStyleIo->success('Structure created successfully!');
    }
}
