<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Elasticsearch\IndexDefinition;
use Shopsys\FrameworkBundle\Model\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Model\Elasticsearch\IndexFacade;
use Shopsys\FrameworkBundle\Model\Elasticsearch\IndexRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractElasticsearchIndexCommand extends Command
{
    private const ARGUMENT_INDEX_NAME = 'name';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexRegistry
     */
    protected $indexRegistry;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexFacade
     */
    protected $indexFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexDefinitionLoader
     */
    protected $indexDefinitionLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexRegistry $indexRegistry
     * @param \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexFacade $indexFacade
     * @param \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        IndexRegistry $indexRegistry,
        IndexFacade $indexFacade,
        IndexDefinitionLoader $indexDefinitionLoader,
        Domain $domain
    ) {
        $this->indexRegistry = $indexRegistry;
        $this->indexFacade = $indexFacade;
        $this->indexDefinitionLoader = $indexDefinitionLoader;
        $this->domain = $domain;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument(
                self::ARGUMENT_INDEX_NAME,
                InputArgument::OPTIONAL,
                $this->getArgumentNameDescription()
            )
            ->setDescription($this->getCommandDescription());
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $symfonyStyleIo = new SymfonyStyle($input, $output);
        $indexName = $input->getArgument(self::ARGUMENT_INDEX_NAME);
        $output->writeln($this->getActionStartedMessage());

        foreach ($this->getAffectedIndexes($indexName) as $index) {
            foreach ($this->domain->getAll() as $domainConfig) {
                $this->executeCommand(
                    $this->indexDefinitionLoader->getIndexDefinition($index, $domainConfig->getId()),
                    $output
                );
            }
        }

        $symfonyStyleIo->success($this->getActionFinishedMessage());
    }

    /**
     * @param string|null $indexName
     * @return \Shopsys\FrameworkBundle\Model\Elasticsearch\AbstractIndex[]
     */
    private function getAffectedIndexes(?string $indexName): array
    {
        if ($indexName) {
            return [$this->indexRegistry->getIndexByIndexName($indexName)];
        }
        return $this->indexRegistry->getRegisteredIndexes();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    abstract protected function executeCommand(IndexDefinition $indexDefinition, OutputInterface $output): void;

    /**
     * @return string
     */
    abstract protected function getCommandDescription(): string;

    /**
     * @return string
     */
    abstract protected function getArgumentNameDescription(): string;

    /**
     * @return string
     */
    abstract protected function getActionStartedMessage(): string;

    /**
     * @return string
     */
    abstract protected function getActionFinishedMessage(): string;
}
