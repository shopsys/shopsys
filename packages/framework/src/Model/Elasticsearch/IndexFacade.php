<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Elasticsearch;

use Symfony\Component\Console\Output\OutputInterface;

class IndexFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexManager
     */
    protected $indexManager;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexManager $indexManager
     */
    public function __construct(IndexManager $indexManager)
    {
        $this->indexManager = $indexManager;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function createByIndexDefinition(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $output->writeln(sprintf(
            'Creating index "%s" on domain "%s"',
            $indexDefinition->getIndex()->getName(),
            $indexDefinition->getDomainId()
        ));

        $this->indexManager->createIndex($indexDefinition);
        $this->indexManager->createAlias($indexDefinition);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function deleteByIndexDefinition(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $output->writeln(sprintf(
            'Deleting index "%s" on domain "%s"',
            $indexDefinition->getIndex()->getName(),
            $indexDefinition->getDomainId()
        ));

        $this->indexManager->deleteIndexByIndexDefinition($indexDefinition);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function exportByIndexDefinition(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $output->writeln(sprintf(
            'Exporting data of "%s" on domain "%s"',
            $indexDefinition->getIndex()->getName(),
            $indexDefinition->getDomainId()
        ));

        $this->indexManager->export($indexDefinition, [], $output);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function migrateByIndexDefinition(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $indexName = $indexDefinition->getIndex()->getName();
        $domainId = $indexDefinition->getDomainId();
        $existingIndexName = $this->indexManager->findCurrentIndexNameForAlias($indexDefinition->getIndexAlias());

        if ($existingIndexName === $indexDefinition->getVersionedIndexName()) {
            $output->writeln(sprintf('Index "%s" on domain "%s" is up to date', $indexName, $domainId));
            return;
        }

        $output->writeln(sprintf('Migrating index "%s" on domain "%s"', $indexName, $domainId));
        $this->indexManager->createIndex($indexDefinition);
        $this->indexManager->reindex($existingIndexName, $indexDefinition->getVersionedIndexName());
        $this->indexManager->createAlias($indexDefinition);
        $this->indexManager->deleteIndex($existingIndexName);
    }
}
