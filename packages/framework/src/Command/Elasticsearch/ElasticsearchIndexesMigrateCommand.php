<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command\Elasticsearch;

use Override;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shopsys:elasticsearch:indexes-migrate',
    description: 'Creates new structure, reindex it from old one, deletes old structure and adds alias to new structure',
)]
class ElasticsearchIndexesMigrateCommand extends AbstractElasticsearchIndexCommand
{
    #[Override]
    protected function executeCommand(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $this->indexFacade->migrate($indexDefinition, $output);
    }

    #[Override]
    protected function getArgumentNameDescription(): string
    {
        return sprintf(
            'Which index should be migrated? Available indexes: "%s"',
            implode(', ', $this->indexRegistry->getRegisteredIndexNames()),
        );
    }

    #[Override]
    protected function getActionStartedMessage(): string
    {
        return 'Migrating indexes';
    }

    #[Override]
    protected function getActionFinishedMessage(): string
    {
        return 'Indexes migrated successfully!';
    }
}
