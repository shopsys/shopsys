<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Symfony\Component\Console\Output\OutputInterface;

class IndexFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository
     */
    protected $indexRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository $indexRepository
     */
    public function __construct(IndexRepository $indexRepository)
    {
        $this->indexRepository = $indexRepository;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function createByIndexDefinition(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $output->writeln(sprintf(
            'Creating index "%s" on domain "%s"',
            $indexDefinition->getIndexName(),
            $indexDefinition->getDomainId()
        ));

        $this->indexRepository->createIndex($indexDefinition);
        $this->indexRepository->createAlias($indexDefinition);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function deleteByIndexDefinition(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $output->writeln(sprintf(
            'Deleting index "%s" on domain "%s"',
            $indexDefinition->getIndexName(),
            $indexDefinition->getDomainId()
        ));

        $this->indexRepository->deleteIndexByIndexDefinition($indexDefinition);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function exportByIndexDefinition(AbstractIndex $index, IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $output->writeln(sprintf(
            'Exporting data of "%s" on domain "%s"',
            $indexDefinition->getIndexName(),
            $indexDefinition->getDomainId()
        ));

        $this->indexRepository->export($index, $indexDefinition, [], $output);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function migrateByIndexDefinition(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $indexName = $indexDefinition->getIndexName();
        $domainId = $indexDefinition->getDomainId();
        $existingIndexName = $this->indexRepository->findCurrentIndexNameForAlias($indexDefinition->getIndexAlias());

        if ($existingIndexName === $indexDefinition->getVersionedIndexName()) {
            $output->writeln(sprintf('Index "%s" on domain "%s" is up to date', $indexName, $domainId));
            return;
        }

        $output->writeln(sprintf('Migrating index "%s" on domain "%s"', $indexName, $domainId));
        $this->indexRepository->createIndex($indexDefinition);
        $this->indexRepository->reindex($existingIndexName, $indexDefinition->getVersionedIndexName());
        $this->indexRepository->createAlias($indexDefinition);
        $this->indexRepository->deleteIndex($existingIndexName);
    }
}
