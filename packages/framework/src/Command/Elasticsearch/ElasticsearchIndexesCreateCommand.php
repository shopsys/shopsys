<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Symfony\Component\Console\Output\OutputInterface;

class ElasticsearchIndexesCreateCommand extends AbstractElasticsearchIndexCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:elasticsearch:indexes-create';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function executeCommand(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $this->indexFacade->createByIndexDefinition($indexDefinition, $output);
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
        return 'Creating structure';
    }

    /**
     * @return string
     */
    protected function getActionFinishedMessage(): string
    {
        return 'Structure created successfully!';
    }
}
