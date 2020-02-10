<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Symfony\Component\Console\Output\OutputInterface;

class ElasticsearchDataExportCommand extends AbstractElasticsearchIndexCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:elasticsearch:data-export';

    /**
     * @inheritDoc
     */
    protected function executeCommand(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $this->indexFacade->exportByIndexDefinition(
            $this->indexRegistry->getIndexByIndexName($indexDefinition->getIndexName()),
            $indexDefinition,
            $output
        );
    }

    /**
     * @inheritDoc
     */
    protected function getCommandDescription(): string
    {
        return 'Export data to Elasticsearch';
    }

    /**
     * @inheritDoc
     */
    protected function getArgumentNameDescription(): string
    {
        return sprintf(
            'Which index data should be exported? Available indexes: "%s"',
            implode(', ', $this->indexRegistry->getRegisteredIndexNames())
        );
    }

    /**
     * @inheritDoc
     */
    protected function getActionStartedMessage(): string
    {
        return 'Exporting data';
    }

    /**
     * @inheritDoc
     */
    protected function getActionFinishedMessage(): string
    {
        return 'Data was exported successfully!';
    }
}
