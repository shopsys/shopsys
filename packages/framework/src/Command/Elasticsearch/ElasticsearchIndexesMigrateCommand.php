<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Symfony\Component\Console\Output\OutputInterface;

class ElasticsearchIndexesMigrateCommand extends AbstractElasticsearchIndexCommand
{
    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnlySniff.ReferenceViaFullyQualifiedName
     */
    protected static $defaultName = 'shopsys:elasticsearch:indexes-migrate';

    /**
     * {@inheritdoc}
     */
    protected function executeCommand(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $this->indexFacade->migrate($indexDefinition, $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandDescription(): string
    {
        return 'Creates new structure, reindexes it from old one, deletes old structure and adds alias to new structure';
    }

    /**
     * {@inheritdoc}
     */
    protected function getArgumentNameDescription(): string
    {
        return sprintf(
            'Which index should be migrated? Available indexes: "%s"',
            implode(', ', $this->indexRegistry->getRegisteredIndexNames())
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getActionStartedMessage(): string
    {
        return 'Migrating indexes';
    }

    /**
     * {@inheritdoc}
     */
    protected function getActionFinishedMessage(): string
    {
        return 'Indexes migrated successfully!';
    }
}
