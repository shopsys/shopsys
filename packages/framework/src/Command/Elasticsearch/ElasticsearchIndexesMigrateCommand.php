<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Symfony\Component\Console\Output\OutputInterface;

class ElasticsearchIndexesMigrateCommand extends AbstractElasticsearchIndexCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:elasticsearch:indexes-migrate';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function executeCommand(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $this->indexFacade->migrateByIndexDefinition($indexDefinition, $output);
    }

    /**
     * @return string
     */
    protected function getCommandDescription(): string
    {
        return 'Migrate indexes in Elasticsearch';
    }

    /**
     * @return string
     */
    protected function getArgumentNameDescription(): string
    {
        return sprintf(
            'Which index will be migrated? Available indexes: "%s"',
            implode(', ', $this->indexRegistry->getRegisteredIndexNames())
        );
    }

    /**
     * @return string
     */
    protected function getActionStartedMessage(): string
    {
        return 'Migrating index';
    }

    /**
     * @return string
     */
    protected function getActionFinishedMessage(): string
    {
        return 'Index migrated successfully!';
    }
}
