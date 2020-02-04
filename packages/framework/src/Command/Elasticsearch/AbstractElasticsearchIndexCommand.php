<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractElasticsearchIndexCommand extends Command
{
    private const ARGUMENT_INDEX_NAME = 'name';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRegistry
     */
    protected $indexRegistry;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade
     */
    protected $indexFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader
     */
    protected $indexDefinitionLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRegistry $indexRegistry
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade $indexFacade
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
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
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
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
        return 0;
    }

    /**
     * @param string|null $indexName
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex[]
     */
    private function getAffectedIndexes(?string $indexName): array
    {
        if ($indexName) {
            return [$this->indexRegistry->getIndexByIndexName($indexName)];
        }
        return $this->indexRegistry->getRegisteredIndexes();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
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
