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
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function executeCommand(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $this->indexFacade->exportByIndexDefinition($indexDefinition, $output);
    }

    /**
     * @return string
     */
    protected function getCommandDescription(): string
    {
        return 'Export data in Elasticsearch';
    }

    /**
     * @return string
     */
    protected function getArgumentNameDescription(): string
    {
        return sprintf(
            'Which index data will be exported? Available indexes: "%s"',
            implode(', ', $this->indexRegistry->getRegisteredIndexNames())
        );
    }

    /**
     * @return string
     */
    protected function getActionStartedMessage(): string
    {
        return 'Exporting data';
    }

    /**
     * @return string
     */
    protected function getActionFinishedMessage(): string
    {
        return 'Data was exported successfully!';
    }
}
