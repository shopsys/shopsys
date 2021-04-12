<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @deprecated This command will be removed in next major version. Use "shopsys:elasticsearch:indexes-migrate" instead.
 */
class ElasticsearchIndexesCreateCommand extends AbstractElasticsearchIndexCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:elasticsearch:indexes-create';

    /**
     * @inheritDoc
     */
    protected function executeCommand(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        trigger_error(
            sprintf(
                'Command "%s" is deprecated and will be removed in next major version. Use "shopsys:elasticsearch:indexes-migrate" instead.',
                static::$defaultName
            ),
            E_USER_DEPRECATED
        );
        $this->indexFacade->create($indexDefinition, $output);
    }

    /**
     * @inheritDoc
     */
    protected function getCommandDescription(): string
    {
        return 'Creates indexes in Elasticsearch';
    }

    /**
     * @inheritDoc
     */
    protected function getArgumentNameDescription(): string
    {
        return sprintf(
            'Which index should be created? Available indexes: "%s"',
            implode(', ', $this->indexRegistry->getRegisteredIndexNames())
        );
    }

    /**
     * @inheritDoc
     */
    protected function getActionStartedMessage(): string
    {
        return 'Creating indexes';
    }

    /**
     * @inheritDoc
     */
    protected function getActionFinishedMessage(): string
    {
        return 'Indexes created successfully!';
    }
}
