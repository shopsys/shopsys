<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command\Elasticsearch;

use Shopsys\FrameworkBundle\Model\Elasticsearch\IndexDefinition;
use Symfony\Component\Console\Output\OutputInterface;

class ElasticsearchIndexesDeleteCommand extends AbstractElasticsearchIndexCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:elasticsearch:indexes-delete';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function executeCommand(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $this->indexFacade->deleteByIndexDefinition($indexDefinition, $output);
    }

    /**
     * @return string
     */
    protected function getCommandDescription(): string
    {
        return 'Creates structure in Elasticsearch';
    }

    /**
     * @return string
     */
    protected function getArgumentNameDescription(): string
    {
        return sprintf(
            'Which index will be created? Available indexes: "%s"',
            implode(', ', $this->indexRegistry->getRegisteredIndexNames())
        );
    }

    /**
     * @return string
     */
    protected function getActionStartedMessage(): string
    {
        return 'Deleting structure';
    }

    /**
     * @return string
     */
    protected function getActionFinishedMessage(): string
    {
        return 'Structure deleted successfully!';
    }
}
