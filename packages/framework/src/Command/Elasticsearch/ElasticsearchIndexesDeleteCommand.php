<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command\Elasticsearch;

use Override;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shopsys:elasticsearch:indexes-delete',
    description: 'Delete indexes from Elasticsearch',
)]
class ElasticsearchIndexesDeleteCommand extends AbstractElasticsearchIndexCommand
{
    #[Override]
    protected function executeCommand(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $this->indexFacade->delete($indexDefinition, $output);
    }

    #[Override]
    protected function getArgumentNameDescription(): string
    {
        return sprintf(
            'Which index should be deleted? Available indexes: "%s"',
            implode(', ', $this->indexRegistry->getRegisteredIndexNames()),
        );
    }

    #[Override]
    protected function getActionStartedMessage(): string
    {
        return 'Deleting indexes';
    }

    #[Override]
    protected function getActionFinishedMessage(): string
    {
        return 'Indexes deleted successfully!';
    }
}
