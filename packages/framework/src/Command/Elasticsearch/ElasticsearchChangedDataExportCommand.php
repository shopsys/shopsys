<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command\Elasticsearch;

use Override;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shopsys:elasticsearch:changed-data-export',
    description: 'Export changed data to Elasticsearch',
)]
class ElasticsearchChangedDataExportCommand extends ElasticsearchDataExportCommand
{
    #[Override]
    protected function executeCommand(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $this->indexFacade->exportChanged(
            $this->indexRegistry->getIndexByIndexName($indexDefinition->getIndexName()),
            $indexDefinition,
            $output,
        );
    }

    #[Override]
    protected function getActionStartedMessage(): string
    {
        return 'Exporting changed data';
    }

    #[Override]
    protected function getActionFinishedMessage(): string
    {
        return 'Changed data was exported successfully!';
    }
}
