<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Symfony\Component\Console\Output\OutputInterface;

class ElasticsearchIndexesDeleteCommand extends AbstractElasticsearchIndexCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:elasticsearch:indexes-delete';

    /**
     * @inheritDoc
     */
    protected function executeCommand(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $this->indexFacade->delete($indexDefinition, $output);
    }

    /**
     * @inheritDoc
     */
    protected function getCommandDescription(): string
    {
        return 'Delete indexes from Elasticsearch';
    }

    /**
     * @inheritDoc
     */
    protected function getArgumentNameDescription(): string
    {
        return sprintf(
            'Which index should be deleted? Available indexes: "%s"',
            implode(', ', $this->indexRegistry->getRegisteredIndexNames())
        );
    }

    /**
     * @inheritDoc
     */
    protected function getActionStartedMessage(): string
    {
        return 'Deleting indexes';
    }

    /**
     * @inheritDoc
     */
    protected function getActionFinishedMessage(): string
    {
        return 'Indexes deleted successfully!';
    }
}
