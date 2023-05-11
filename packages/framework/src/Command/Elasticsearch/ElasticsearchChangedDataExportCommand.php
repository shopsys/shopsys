<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Symfony\Component\Console\Output\OutputInterface;

class ElasticsearchChangedDataExportCommand extends ElasticsearchDataExportCommand
{
    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnlySniff.ReferenceViaFullyQualifiedName
     */
    protected static $defaultName = 'shopsys:elasticsearch:changed-data-export';

    /**
     * {@inheritdoc}
     */
    protected function executeCommand(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $this->indexFacade->exportChanged(
            $this->indexRegistry->getIndexByIndexName($indexDefinition->getIndexName()),
            $indexDefinition,
            $output
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandDescription(): string
    {
        return 'Export changed data to Elasticsearch';
    }

    /**
     * {@inheritdoc}
     */
    protected function getActionStartedMessage(): string
    {
        return 'Exporting changed data';
    }

    /**
     * {@inheritdoc}
     */
    protected function getActionFinishedMessage(): string
    {
        return 'Changed data was exported successfully!';
    }
}
