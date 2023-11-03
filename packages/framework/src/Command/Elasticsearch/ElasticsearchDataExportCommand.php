<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexExportedEvent;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[AsCommand(name: 'shopsys:elasticsearch:data-export')]
class ElasticsearchDataExportCommand extends AbstractElasticsearchIndexCommand
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRegistry $indexRegistry
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade $indexFacade
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        IndexRegistry $indexRegistry,
        IndexFacade $indexFacade,
        IndexDefinitionLoader $indexDefinitionLoader,
        Domain $domain,
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($indexRegistry, $indexFacade, $indexDefinitionLoader, $domain);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addOption(
            self::OPTION_DOMAIN_ID,
            null,
            InputOption::VALUE_REQUIRED,
            'Limit command to only one domain. Products will not be marked as exported.',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function executeForIndex(OutputInterface $output, AbstractIndex $index, ?int $domainId = null): void
    {
        parent::executeForIndex($output, $index, $domainId);

        if ($domainId === null) {
            $this->eventDispatcher->dispatch(
                new IndexExportedEvent($index),
                IndexExportedEvent::INDEX_EXPORTED,
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function executeCommand(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $this->indexFacade->export(
            $this->indexRegistry->getIndexByIndexName($indexDefinition->getIndexName()),
            $indexDefinition,
            $output,
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandDescription(): string
    {
        return 'Export data to Elasticsearch';
    }

    /**
     * {@inheritdoc}
     */
    protected function getArgumentNameDescription(): string
    {
        return sprintf(
            'Which index data should be exported? Available indexes: "%s"',
            implode(', ', $this->indexRegistry->getRegisteredIndexNames()),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getActionStartedMessage(): string
    {
        return 'Exporting data';
    }

    /**
     * {@inheritdoc}
     */
    protected function getActionFinishedMessage(): string
    {
        return 'Data was exported successfully!';
    }
}
